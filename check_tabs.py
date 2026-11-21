import os, re

tabs_dir = 'resources/views/portal/admin/tabs'
for f in sorted(os.listdir(tabs_dir)):
    if not f.endswith('.blade.php'): continue
    path = os.path.join(tabs_dir, f)
    with open(path, 'r', encoding='utf-8') as fp:
        content = fp.read()
    ids = re.findall(r'id=["\'](tab-[^"\']+)["\']', content)
    print(f'{f}: {ids}')
