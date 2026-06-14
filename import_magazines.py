import os
import json
import glob
import fitz  # PyMuPDF
import uuid
from datetime import datetime
import re

# Paths
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
UPLOADS_MAGS = os.path.join(BASE_DIR, 'uploads', 'magazines')
UPLOADS_IMGS = os.path.join(BASE_DIR, 'uploads', 'images')
DB_FILE = os.path.join(BASE_DIR, 'data', 'database.json')

# Ensure image directory exists
os.makedirs(UPLOADS_IMGS, exist_ok=True)

# Load Database
if not os.path.exists(DB_FILE):
    print("Error: database.json not found!")
    exit(1)

with open(DB_FILE, 'r', encoding='utf-8') as f:
    db = json.load(f)

if 'magazines' not in db:
    db['magazines'] = []

# Collect existing pdf_paths to avoid duplicates
existing_pdfs = set()
for mag in db['magazines']:
    path = mag.get('pdf_path', '')
    path = path.replace('\\', '/')
    existing_pdfs.add(path)

# Iterate over PDFs
pdf_files = glob.glob(os.path.join(UPLOADS_MAGS, '*.pdf'))
count_added = 0
count_skipped = 0

for pdf_path in pdf_files:
    filename = os.path.basename(pdf_path)
    web_pdf_path = f"uploads/magazines/{filename}"
    
    if web_pdf_path in existing_pdfs:
        count_skipped += 1
        continue
    
    print(f"Processing: {filename}")
    
    # Title from filename: Remove .pdf, replace dashes with spaces
    title = re.sub(r'\.pdf$', '', filename, flags=re.IGNORECASE)
    title = title.replace('-', ' ')
    
    # Render first page as cover
    try:
        doc = fitz.open(pdf_path)
        if len(doc) > 0:
            page = doc.load_page(0)
            pix = page.get_pixmap()
            cover_filename = f"cover_{uuid.uuid4().hex[:8]}.jpg"
            cover_disk_path = os.path.join(UPLOADS_IMGS, cover_filename)
            pix.save(cover_disk_path)
            web_cover_path = f"uploads/images/{cover_filename}"
        else:
            print(f"Warning: PDF {filename} has no pages.")
            web_cover_path = "uploads/images/magazine_cover_default.png"
    except Exception as e:
        print(f"Error processing PDF {filename}: {e}")
        web_cover_path = "uploads/images/magazine_cover_default.png"
        
    # Generate ID and entry
    mag_id = 'mag-' + uuid.uuid4().hex[:12]
    today_str = datetime.now().strftime('%Y-%m-%d')
    
    new_mag = {
        'id': mag_id,
        'title': title,
        'pdf_path': web_pdf_path,
        'cover_path': web_cover_path,
        'pub_date': today_str,
        'pages': [] 
    }
    
    db['magazines'].append(new_mag)
    existing_pdfs.add(web_pdf_path)
    count_added += 1

# Save database
with open(DB_FILE, 'w', encoding='utf-8') as f:
    json.dump(db, f, ensure_ascii=False, indent=4)

print(f"Import completed! Added: {count_added}. Skipped (already in DB): {count_skipped}.")
