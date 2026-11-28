"""Append-only recovery evidence. Never treats a prior pass as permission to replay writes."""
import argparse, datetime, hashlib, json, pathlib, subprocess

ROOT = pathlib.Path(__file__).resolve().parents[1]

def git(*args):
    return subprocess.check_output(['git', '-c', 'safe.directory='+str(ROOT), *args], cwd=ROOT, text=True).strip()

def main():
    p=argparse.ArgumentParser()
    p.add_argument('--stage', required=True)
    p.add_argument('--completed', required=True)
    p.add_argument('--pending', required=True)
    p.add_argument('--next', required=True)
    p.add_argument('--tests', required=True)
    args=p.parse_args()
    settings=json.loads((ROOT/'.native-runtime/settings.json').read_text())
    if settings['purpose']!='multifactura-native-v7' or pathlib.Path(settings['root']).resolve()!=ROOT:
        raise RuntimeError('Wrong lab')
    files=git('ls-files','--modified','--others','--exclude-standard').splitlines()
    hashes={f:hashlib.sha256((ROOT/f).read_bytes()).hexdigest() for f in files if (ROOT/f).is_file()}
    row=dict(vars(args), timestamp=datetime.datetime.now(datetime.timezone.utc).isoformat(),
             branch=git('branch','--show-current'), commit=git('rev-parse','HEAD'), files=hashes,
             ports={'mysql':13323,'http':8879}, pid_markers={})
    for service in ('mysql','web'):
        path=ROOT/'.native-runtime'/f'{service}.pid'
        row['pid_markers'][service]=path.read_text().strip() if path.exists() else None
    row['resume_policy']='PID markers require live ownership verification; never rerun migrations or stateful tests solely from this checkpoint.'
    target=ROOT/'.native-runtime/checkpoints'
    target.mkdir(exist_ok=True)
    name=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%S%fZ')
    with (target/(name+'.json')).open('x',encoding='utf-8') as f:
        json.dump(row,f,indent=2)
    print('Checkpoint saved: '+name+'; '+str(len(hashes))+' file hashes; no secrets included.')

if __name__=='__main__': main()
