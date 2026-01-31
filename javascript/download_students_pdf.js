document.getElementById("pdf_button").addEventListener("click", function () {

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a4",
    });

    pdf.setFontSize(16);
    pdf.text("Alunni Censiti", 10, 10);

    const table = document.getElementById("student_table");

    pdf.autoTable({
        html: table,
        headStyles: {
            fillColor: [0, 0, 0],
            textColor: 255,
        },
    });

    pdf.save("students_table.pdf");
});
