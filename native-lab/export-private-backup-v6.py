"""Export verified recovery evidence and V7 Git bundle outside the working tree."""
from pathlib import Path
import argparse,datetime,hashlib,json,shutil,subprocess
ROOT=Path(__file__).resolve().parents[1];RUNTIME=ROOT/'.native-runtime'

def git(*args):return subprocess.check_output(['git','-c','safe.directory='+str(ROOT),*args],cwd=ROOT,text=True).strip()
def sha(path):return hashlib.sha256(path.read_bytes()).hexdigest()

def main():
 p=argparse.ArgumentParser();p.add_argument('recovery');a=p.parse_args()
 source=Path(a.recovery).resolve()
 if source.parent!=RUNTIME.resolve() or not source.name.startswith('recovery-'):raise ValueError('Only owned V7 recovery folders allowed')
 verified=json.loads((source/'manifest.json').read_text())
 if verified.get('all_row_counts_and_digests_match') is not True or verified.get('databases')!=5:raise ValueError('Verified restoration required')
 if sha(source/'fictional-v7.sql')!=verified['dump_sha256']:raise ValueError('Dump checksum changed')
 parent=Path(r'C:\Users\luis castillo\MultiFactura-private-backups')
 if not parent.is_dir():raise ValueError('Expected private backup root missing')
 target=parent/('native-v7-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));target.mkdir(exist_ok=False)
 user=subprocess.check_output(['whoami'],text=True).strip()
 subprocess.run(['icacls',str(target),'/inheritance:r','/grant:r',user+':(OI)(CI)F','*S-1-5-18:(OI)(CI)F','*S-1-5-32-544:(OI)(CI)F'],capture_output=True,check=True)
 files={}
 for name in ['fictional-v7.sql','source-digest.json','restored-digest.json','manifest.json']:
  dest=target/name;shutil.copy2(source/name,dest)
  if sha(dest)!=sha(source/name):raise ValueError('Backup copy hash mismatch')
  files[name]=sha(dest)
 for name in ['settings.json','admin.json','accounts.json','ACCESS.txt','provision.json','fourth-account.json','ollama-authorization.json','ollama-gateway.json']:
  source_file=RUNTIME/name
  if source_file.exists():
   dest=target/('private-'+name);shutil.copy2(source_file,dest)
   if sha(dest)!=sha(source_file):raise ValueError('Private configuration backup mismatch')
   files[dest.name]=sha(dest)
 base='a88b7555f81710b3703edc90405af59c206d31c3'
 bundle=target/'v7.bundle';git('bundle','create',str(bundle),'HEAD','^'+base)
 git('bundle','verify',str(bundle));files[bundle.name]=sha(bundle)
 report={'commit':git('rev-parse','HEAD'),'base_required':base,'branch':git('branch','--show-current'),'files':files,'database_restore_verified':verified}
 (target/'BACKUP_MANIFEST.json').write_text(json.dumps(report,indent=2))
 print('Private V7 backup verified: '+str(target)+'; '+str(len(files))+' hashes; commit '+report['commit'])

if __name__=='__main__':main()
