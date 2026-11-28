"""Exclusive offline test stage: logical V7 backup and isolated MySQL restore, no source writes."""
from pathlib import Path
import datetime,hashlib,json,os,secrets,socket,subprocess,time,sys,atexit
ROOT=Path(__file__).resolve().parents[1]
MYSQL=Path(r'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin')
PHP=r'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
DBS=['mf_v71_system','mf_v71_tattoo','mf_v71_dental','mf_v71_commerce','mf_v71_cuarta']

def main():
 settings=json.loads((ROOT/'.native-runtime/settings.json').read_text())
 assert settings['purpose']=='multifactura-native-v7-1' and Path(settings['root']).resolve()==ROOT
 with socket.socket() as s:
  assert s.connect_ex(('127.0.0.1',13326))!=0,'Restore port occupied; do not touch existing service'
 target=ROOT/'.native-runtime'/('recovery-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'))
 target.mkdir(exist_ok=False);data=target/'mysql-data';data.mkdir()
 admin=json.loads((ROOT/'.native-runtime/admin.json').read_text())
 def options(name,port,password):
  assert all(c in '0123456789abcdef' for c in password)
  f=target/(name+'.cnf');f.write_text(f'[client]\nhost=127.0.0.1\nport={port}\nuser=root\npassword={password}\nprotocol=tcp\n');return f
 source=options('source',13325,admin['password']);password=secrets.token_hex(24);restored=options('restored',13326,password)
 def digest(name,port,password,datadir):
  f=target/(name+'-private.json');f.write_text(json.dumps({'port':port,'user':'root','password':password,'datadir':str(datadir)}))
  p=subprocess.run([PHP,'native-lab/recovery-digest.php',str(f)],cwd=ROOT,capture_output=True,timeout=180)
  if p.returncode:raise RuntimeError('Digest failed; inspect private runtime')
  out=target/(name+'-digest.json');out.write_bytes(p.stdout);return json.loads(p.stdout)
 before=digest('source',13325,admin['password'],ROOT/'.native-runtime/mysql-data')
 dbs=list(before)
 if set(dbs) not in [set(DBS),set(DBS[:-1])]:raise RuntimeError('Unexpected database inventory')
 dump=target/'fictional-v7.sql'
 with dump.open('xb') as output,(target/'dump.err.log').open('xb') as error:
  p=subprocess.run([str(MYSQL/'mysqldump.exe'),'--defaults-extra-file='+str(source),'--single-transaction','--skip-comments','--routines','--triggers','--hex-blob','--set-gtid-purged=OFF','--column-statistics=0','--no-tablespaces','--databases',*dbs],stdout=output,stderr=error,timeout=180)
  if p.returncode:raise RuntimeError('Dump failed; inspect private log')
 if '--offline-source' in sys.argv:
  # Stop only the already-verified V7 server, after its snapshot is complete.
  marker=target/'source-offline.json';marker.write_text(json.dumps({'source_port':13325,'next':'native-lab/resume-v6.ps1 (adapted V7; no migrations)'}))
  subprocess.run([str(MYSQL/'mysqladmin.exe'),'--defaults-extra-file='+str(source),'shutdown'],capture_output=True,check=True,timeout=30)
  def resume_source():
   with (target/'source-resume.log').open('wb') as resume_log:
    p=subprocess.run(['powershell','-NoProfile','-File',str(ROOT/'native-lab/resume-v6.ps1')],cwd=ROOT,stdout=resume_log,stderr=resume_log,timeout=90)
   marker.write_text(json.dumps({'source_port':13325,'resumed':p.returncode==0}))
   print('Source V7 resumed:',p.returncode==0,flush=True)
  atexit.register(resume_source)
 with (target/'initialize.log').open('xb') as log:
  subprocess.run([str(MYSQL/'mysqld.exe'),'--no-defaults','--initialize-insecure','--basedir='+str(MYSQL.parent),'--datadir='+str(data)],stdout=log,stderr=log,check=True,timeout=120,creationflags=subprocess.CREATE_NO_WINDOW)
 log=(target/'restore-server.log').open('xb')
 process=subprocess.Popen([str(MYSQL/'mysqld.exe'),'--no-defaults','--basedir='+str(MYSQL.parent),'--datadir='+str(data),'--port=13326','--bind-address=127.0.0.1','--mysqlx=0','--restrict-fk-on-non-standard-key=OFF','--innodb-buffer-pool-size=33554432','--max-connections=10','--performance-schema=OFF','--console'],stdout=log,stderr=log,creationflags=subprocess.CREATE_NO_WINDOW)
 try:
  for _ in range(80):
   with socket.socket() as s:
    if s.connect_ex(('127.0.0.1',13326))==0:break
   if process.poll() is not None:raise RuntimeError('Restore MySQL exited')
   time.sleep(.25)
  # New empty server only. Immediately set its independent, random local password.
  subprocess.run([str(MYSQL/'mysql.exe'),'--no-defaults','-h127.0.0.1','-P13326','-uroot'],input=("ALTER USER 'root'@'localhost' IDENTIFIED BY '"+password+"';").encode(),capture_output=True,check=True,timeout=15)
  with dump.open('rb') as inp,(target/'restore.err.log').open('xb') as err:
   p=subprocess.run([str(MYSQL/'mysql.exe'),'--defaults-extra-file='+str(restored)],stdin=inp,stdout=subprocess.DEVNULL,stderr=err,timeout=300)
   if p.returncode:raise RuntimeError('Restore failed; inspect private log')
  after=digest('restored',13326,password,data)
  if before!=after:raise RuntimeError('Restored row digests differ; no recovery approval')
  manifest={'databases':len(before),'tables':sum(len(x) for x in before.values()),'dump_sha256':hashlib.sha256(dump.read_bytes()).hexdigest(),'all_row_counts_and_digests_match':True,'source_port':13325,'restore_port':13326}
  (target/'manifest.json').write_text(json.dumps(manifest,indent=2));print(json.dumps(manifest),flush=True)
 finally:
  subprocess.run([str(MYSQL/'mysqladmin.exe'),'--defaults-extra-file='+str(restored),'shutdown'],capture_output=True,timeout=20)
  try:process.wait(timeout=20)
  except subprocess.TimeoutExpired:process.terminate();process.wait(timeout=10)
  log.close()
 print('Recovery evidence: '+target.name)

if __name__=='__main__':main()
