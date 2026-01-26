document.getElementById("get-participation-certificate").addEventListener("click", async function (e) {
    e.preventDefault();

    const userName = String(this.dataset.userName).trim();
    const instituteTeachersRaw = String(this.dataset.istituteTeacher || "");
    const extraNames = instituteTeachersRaw.split(",").map(n => n.trim()).filter(n => n.length > 0);

    // Tutti i nomi da processare
    const allNames = [userName, ...extraNames];

    // Carica il PDF base
    let pdfResponse = await fetch('./documents/CoRiPo_attestato_partecipazione.pdf');
    let pdfArrayBuffer = await pdfResponse.arrayBuffer();

    // Inizializza zip
    const zip = new JSZip();

    for (const name of allNames) {
        const { PDFDocument, degrees } = PDFLib;
        const pdfDoc = await PDFDocument.load(pdfArrayBuffer);
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
        const safeName = name.replace(/[\\/:*?"<>|]/g, "_"); // evitare caratteri non validi
        zip.file(`Attestato_${safeName}.pdf`, pdfBytes);
    }

    // Crea e scarica lo ZIP
    zip.generateAsync({ type: "blob" }).then(function (blob) {
        const zipLink = document.createElement("a");
        zipLink.href = URL.createObjectURL(blob);
        zipLink.download = "Attestati.zip";
        document.body.appendChild(zipLink);
        zipLink.click();
        document.body.removeChild(zipLink);
    });
});
