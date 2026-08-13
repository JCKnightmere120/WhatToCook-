import { Injectable } from '@angular/core';

export interface ExportLine { title: string; detail?: string; checked?: boolean; }

@Injectable({ providedIn: 'root' })
export class ExportService {
  downloadText(filename: string, lines: ExportLine[]): void {
    this.download(filename, new Blob([lines.map(line => `${line.checked ? '[x]' : '[ ]'} ${line.title}${line.detail ? ` — ${line.detail}` : ''}`).join('\n')], { type: 'text/plain;charset=utf-8' }));
  }
  downloadImage(filename: string, title: string, lines: ExportLine[]): void {
    const height = Math.max(180, 104 + lines.length * 34);
    const rows = lines.map((line, i) => `<text x="36" y="${88 + i * 34}" font-size="17" fill="#26352a">${this.escape(line.checked ? '☑ ' : '☐ ')}${this.escape(line.title)}${line.detail ? this.escape(` — ${line.detail}`) : ''}</text>`).join('');
    this.download(filename, new Blob([`<svg xmlns="http://www.w3.org/2000/svg" width="900" height="${height}"><rect width="100%" height="100%" fill="#fffdf7"/><text x="36" y="50" font-family="Arial" font-size="28" font-weight="bold" fill="#1f5139">${this.escape(title)}</text>${rows}</svg>`], { type: 'image/svg+xml;charset=utf-8' }));
  }
  print(title: string, lines: ExportLine[]): void {
    const popup = window.open('', '_blank', 'noopener,noreferrer'); if (!popup) return;
    const rows = lines.map(line => `<li>${this.escape(line.checked ? '☑ ' : '☐ ')}${this.escape(line.title)}${line.detail ? ` <small>${this.escape(line.detail)}</small>` : ''}</li>`).join('');
    popup.document.write(`<!doctype html><title>${this.escape(title)}</title><style>body{font:16px Arial;padding:24px;color:#26352a}li{margin:10px 0}small{color:#5f6b61}</style><h1>${this.escape(title)}</h1><ul>${rows}</ul>`); popup.document.close(); popup.focus(); popup.print();
  }
  private download(filename: string, blob: Blob): void { const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = filename; link.click(); URL.revokeObjectURL(link.href); }
  private escape(value: string): string { return value.replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char] || char); }
}
