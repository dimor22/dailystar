from pathlib import Path
import sys
import subprocess

md_path = Path('/Users/user/DailyStars/docs/gamification-outline.md')
pdf_path = Path('/Users/user/DailyStars/docs/gamification-outline.pdf')

try:
    from reportlab.lib.pagesizes import LETTER
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib.units import inch
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer
except Exception:
    subprocess.check_call([sys.executable, '-m', 'pip', 'install', '--user', 'reportlab'])
    from reportlab.lib.pagesizes import LETTER
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib.units import inch
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer

text = md_path.read_text(encoding='utf-8').splitlines()

styles = getSampleStyleSheet()
body = ParagraphStyle(
    'Body',
    parent=styles['BodyText'],
    fontName='Helvetica',
    fontSize=10.5,
    leading=14,
    spaceAfter=6,
)
heading1 = ParagraphStyle(
    'Heading1Custom',
    parent=styles['Heading1'],
    fontName='Helvetica-Bold',
    fontSize=18,
    leading=22,
    spaceAfter=12,
)
heading2 = ParagraphStyle(
    'Heading2Custom',
    parent=styles['Heading2'],
    fontName='Helvetica-Bold',
    fontSize=13,
    leading=17,
    spaceBefore=6,
    spaceAfter=8,
)
heading3 = ParagraphStyle(
    'Heading3Custom',
    parent=styles['Heading3'],
    fontName='Helvetica-Bold',
    fontSize=11.5,
    leading=15,
    spaceBefore=4,
    spaceAfter=6,
)

story = []
for line in text:
    s = line.strip()
    if not s:
        story.append(Spacer(1, 4))
        continue

    s = s.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')

    if s.startswith('# '):
        story.append(Paragraph(s[2:], heading1))
    elif s.startswith('## '):
        story.append(Paragraph(s[3:], heading2))
    elif s.startswith('### '):
        story.append(Paragraph(s[4:], heading3))
    elif s.startswith('- '):
        story.append(Paragraph(f'• {s[2:]}', body))
    else:
        story.append(Paragraph(s, body))

doc = SimpleDocTemplate(
    str(pdf_path),
    pagesize=LETTER,
    leftMargin=0.8 * inch,
    rightMargin=0.8 * inch,
    topMargin=0.75 * inch,
    bottomMargin=0.75 * inch,
)
doc.build(story)
print(f'Created: {pdf_path}')
