"""Read-only scan of newly reachable Git blobs; never prints secret values.

Usage: python audit/20261120/verify-publication.py github/main HEAD
This is a targeted check, not proof that historical secrets have been revoked.
"""
import re
import subprocess
import sys

GIT = ['git', '-c', 'safe.directory=C:/laragon/www/multifacturaperunew']
base, tip = sys.argv[1:3]


def git(*args):
    return subprocess.check_output(GIT + list(args))


patterns = {
    'private-key': re.compile(rb'-----BEGIN (?:RSA |EC |OPENSSH |ENCRYPTED )?PRIVATE KEY-----'),
    'provider-key': re.compile(rb'(?:sk-(?:proj-)?[A-Za-z0-9_-]{28,}|gh[pousr]_[A-Za-z0-9]{30,}|AKIA[A-Z0-9]{16})'),
}
known = []
for path in ['.env.example', '.env.dusk.example']:
    for line in git('show', base + ':' + path).splitlines():
        key, sep, value = line.partition(b'=')
        value = value.strip().strip(b'\'"')
        if sep and re.search(rb'(KEY|PASSWORD|SECRET|TOKEN)', key) and len(value) >= 12:
            if b'${' not in value and value not in [b'tattoo_inbox_verify_token']:
                known.append(value)
for path, pattern in [
    ('app/CoreFacturalo/Services/Dni/Jne.php', rb"\$requestToken\s*=\s*'([^']+)'"),
    ('app/CoreFacturalo/Services/IntegratedQuery/AuthApi.php', rb"'client_secret'\s*=>\s*'([^']+)'"),
]:
    match = re.search(pattern, git('show', base + ':' + path))
    if match:
        known.append(match.group(1))

objects = git('rev-list', '--objects', tip, '^' + base).splitlines()
failures = []
scanned = 0
for entry in objects:
    oid, _, path = entry.partition(b' ')
    if git('cat-file', '-t', oid.decode()).strip() != b'blob':
        continue
    data = git('cat-file', 'blob', oid.decode())
    scanned += 1
    for label, pattern in patterns.items():
        if pattern.search(data):
            failures.append((path.decode(errors='replace'), label))
    if any(secret in data for secret in known):
        failures.append((path.decode(errors='replace'), 'inherited-literal-secret'))
    name = path.decode(errors='replace')
    if name == '.env' or name.endswith(('.sql', '.pfx', '.p12')) or name.startswith(('storage/app/', 'storage/logs/')):
        failures.append((name, 'private-path'))

print('New reachable blobs scanned:', scanned)
for path, label in failures:
    print('BLOCKED:', path, label, '(value withheld)')
if failures:
    raise SystemExit(1)
print('PASS: no targeted secret markers, inherited literal secrets or private paths in new blobs.')
