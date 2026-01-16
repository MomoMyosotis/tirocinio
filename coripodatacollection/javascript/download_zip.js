document.getElementById("get-pdf-zip").addEventListener("click", async function () {
    let istituto = document.getElementById("istituto").innerText;

    let dati = [];
    document.querySelectorAll("[id^='code']").forEach(el => {
        let num = el.id.replace("code", "");
        let codice = el.innerText;
        let plesso = document.getElementById("plesso" + num)?.innerText || "";
        let nome = document.getElementById("nome" + num)?.innerText || "";
        let cognome = document.getElementById("cognome" + num)?.innerText || "";
        let classe = document.getElementById("classe" + num)?.innerText || "";

        dati.push([codice, istituto.concat('-', plesso), nome, cognome, classe]);
    });

    let pdfResponse = await fetch('./documents/lettera_ai_genitori.pdf');
    let pdfArrayBuffer = await pdfResponse.arrayBuffer();

    let { PDFDocument } = PDFLib;
    let zip = new JSZip();

    let lastclone;
    for (let i = 0; i < dati.length; i++) {
        let pdfDoc = await PDFDocument.load(pdfArrayBuffer);
        let pdfClone = await createModifiedPdf(pdfDoc, dati[i]);

        let [codice, plesso, nome, cognome, classe] = dati[i];
        let fileName = `${plesso}-${classe}-${nome}-${cognome}.pdf`;

        zip.file(fileName, pdfClone);
    }

    zip.generateAsync({ type: "blob" }).then(function (content) {
        saveAs(content, "lettere.zip");
    });
});

async function createModifiedPdf(pdfDoc, dati) {
    const [codice, plesso, nome, cognome, classe] = dati;

    let page = pdfDoc.getPages()[0];
    const { width, height } = page.getSize();

    page.drawText(`${cognome.concat(' ', nome, ',')}`, {
        x: 210,
        y: height - 183,
        size: 10,
    });
    page.drawText(`${plesso}`, {
        x: 210,
        y: height - 611,
        font: await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold),
        size: 10,
    });
    page.drawText(`${classe}`, {
        x: 210,
        y: height - 623,
        font: await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold),
        size: 10,
    });
    page.drawText(`${codice}`, {
        x: 210,
        y: height - 635,
        font: await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold),
        size: 10,
    });

    const pdfBytes = await pdfDoc.save();
    return pdfBytes;
}
