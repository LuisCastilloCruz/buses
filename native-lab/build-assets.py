import os,subprocess
from pathlib import Path
root=Path(__file__).resolve().parents[1]
node=root/'.native-runtime/node14.exe'
if not node.exists():raise SystemExit('Use portable Node 14.21.3 for the existing node-sass 4.14.1 dependencies; see README.')
env=dict(os.environ,NODE_ENV='production',NODE_OPTIONS='--max-old-space-size=6144')
with (root/'.native-runtime/build-release.log').open('w',encoding='utf-8') as log:
 result=subprocess.run([str(node),'node_modules/webpack/bin/webpack.js','--config=node_modules/laravel-mix/setup/webpack.config.js','--env.mixfile=webpack.native.js'],cwd=root,env=env,stdout=log,stderr=subprocess.STDOUT)
print('Webpack full native assets exit:',result.returncode)
raise SystemExit(result.returncode)
