document.getElementById("pdf_button").addEventListener("click", function () {

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a1",
    });

    pdf.setFontSize(16);
    pdf.text("Risultati Inseriti", 10, 10);

    const table = document.getElementById("result_table");

    pdf.autoTable({
        html: table,
        headStyles: {
            fillColor: [0, 0, 0],
            textColor: 255,
            lineColor: [255, 255, 255],
            lineWidth: 0.5,
            halign: "center"
        },
        bodyStyles: {
            halign: "right",
        },
    });

    pdf.save("results_table.pdf");
});
