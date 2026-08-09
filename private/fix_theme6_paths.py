from pathlib import Path
import re

root = Path(r'c:\xampp\htdocs\freshout\themes\theme6')
files = list(root.rglob('*.php'))

for path in files:
    text = path.read_text(encoding='utf-8')
    updated = text

    marker = '<div id="search-4" class="widget widget_search">'
    if marker in updated:
        start = updated.find(marker)
        end = updated.find('</div>', start)
        if end != -1:
            updated = updated[:start] + '<?php echo $translate; ?>' + updated[end + 6:]

    if '<base href="<?php echo $url; ?>/" />' not in updated:
        updated = updated.replace(
            '<meta name="viewport" content="width=device-width, initial-scale=1">',
            '<meta name="viewport" content="width=device-width, initial-scale=1">\n    <base href="<?php echo $url; ?>/" />',
            1,
        )

    if updated != text:
        path.write_text(updated, encoding='utf-8')
        print(path)
