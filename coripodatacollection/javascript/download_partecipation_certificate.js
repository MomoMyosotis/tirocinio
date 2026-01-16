document.getElementById("get-participation-certificate").addEventListener("click", async function (e) {

    e.preventDefault();
    // 1. Ottieni il nome utente dall'elemento HTML (assumendo un <span> o <input>)
    let name = String(this.dataset.userName);

    // 2. Carica il file PDF originale
    let pdfResponse = await fetch('./documents/CoRiPo_attestato_partecipazione.pdf');
    let pdfArrayBuffer = await pdfResponse.arrayBuffer();

    // 3. Usa PDFLib per modificare il PDF
    const { PDFDocument } = PDFLib;
    const pdfDoc = await PDFDocument.load(pdfArrayBuffer);
    const modifiedPdfBytes = await createModifiedPdf(pdfDoc, name);

    // 4. Crea un blob e innesca il download
    const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Attestato_${name}.pdf`; // nome del file personalizzato
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

async function createModifiedPdf(pdfDoc, name) {
    const { PDFDocument, degrees } = PDFLib;
    const page = pdfDoc.getPages()[0];
    const { width, height } = page.getSize();

    const font = await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaOblique);
    const fontSize = 40;
    const textWidth = font.widthOfTextAtSize(name, fontSize);

    let rotate = degrees(-90);
    let x = width - 287;
    let y = (height + textWidth) / 2;

    page.drawText(name, {
        x: x,
        y: y,
        size: fontSize,
        font: font,
        rotate: rotate,
    });
    const pdfBytes = await pdfDoc.save();
    return pdfBytes;
}