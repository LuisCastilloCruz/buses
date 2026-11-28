from pathlib import Path
import subprocess,sys
root=Path(__file__).resolve().parents[1]
php=r'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe'
try:
 subprocess.run([php,'native-lab/v71-connection-limit.php','64'],cwd=root,check=True)
 result=subprocess.run([sys.executable,'-X','utf8','qa/performance/concurrency.py',*sys.argv[1:]],cwd=root)
finally:
 subprocess.run([php,'native-lab/v71-connection-limit.php','20'],cwd=root,check=True)
sys.exit(result.returncode)
