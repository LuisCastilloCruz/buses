"""Conservative staged-change gate; reports paths/rules, never credential values."""
import argparse,pathlib,re,subprocess,sys
ROOT=pathlib.Path(__file__).resolve().parents[1]
def git(*args):return subprocess.check_output(['git','-c','safe.directory='+str(ROOT),*args],cwd=ROOT)
parser=argparse.ArgumentParser();parser.add_argument('--base');args=parser.parse_args()
if args.base and not re.fullmatch('[a-f0-9]{40}',args.base):raise ValueError('Full base commit SHA required')
names=(git('diff','--name-only','--diff-filter=ACMR','-z',args.base,'HEAD') if args.base else git('diff','--cached','--name-only','--diff-filter=ACMR','-z')).decode().split('\0')
rules={
 'private-key':rb'-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----',
 'openai-like':rb'\bsk-(?:proj-)?[A-Za-z0-9_-]{28,}',
 'github-like':rb'\b(?:ghp_|github_pat_)[A-Za-z0-9_]{30,}',
 'aws-access':rb'\bAKIA[A-Z0-9]{16}\b',
 'meta-like':rb'\bEAA[A-Za-z0-9]{70,}'
}
fail=[];count=0
for name in filter(None,names):
 p=pathlib.PurePosixPath(name);count+=1
 if '.native-runtime' in p.parts or p.name.startswith('.env') or p.suffix.lower() in ['.sql','.sqlite','.db','.zip','.bundle','.pem','.key']:
  fail.append((name,'private-artifact'));continue
 data=git('show',('HEAD:' if args.base else ':')+name)
 if p.suffix.lower()=='.png':
  if not data.startswith(b'\x89PNG\r\n\x1a\n'):fail.append((name,'invalid-PNG'))
  continue
 for rule,pattern in rules.items():
  if re.search(pattern,data):fail.append((name,rule))
for name,rule in fail:print('BLOCKED',rule,name)
print(f'Staged scan: {count} files, {len(fail)} findings. Pattern scan does not certify historical secrets or image privacy.')
sys.exit(bool(fail))
