document.getElementsByName("pdf_button").forEach(button => {
    button.addEventListener("click", function () {

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();

        pdf.setFontSize(20);
        const pageWidth = pdf.internal.pageSize.getWidth();
        pdf.text("Consenso partecipazione", pageWidth / 2, 20, { align: "center" });

        const progetto = button.getAttribute("progetto");
        const classe = button.getAttribute("classe");
        const istituto = button.getAttribute("istituto");
        const plesso = button.getAttribute("plesso");

        const testo = `Il sottoscritto ______________________________ genitore dell'alunno/a _________________________________ della classe ${classe} dell'istituto scolastico ${istituto}, plesso ${plesso} concede il consenso per la partecipazione del proprio figlio/a al progetto di ricerca ${progetto}.\n\n Firma del genitore o del tutore \n_______________________________`;

        const pageMargin = 10; // Margine sinistro e destro della pagina
        const contentWidth = pageWidth - 2 * pageMargin + 10; // Larghezza utilizzabile
        const lines = pdf.splitTextToSize(testo, contentWidth);

        pdf.setFontSize(12);
        pdf.setLineHeightFactor(1.5);
        pdf.text(lines, 10, 30);

        pdf.save("consenso.pdf");
    });
});
