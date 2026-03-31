const fs = require('fs');
const PDFParser = require("pdf2json");

const pdfParser = new PDFParser(this, 1);

pdfParser.on("pdfParser_dataError", errData => console.error(errData.parserError));
pdfParser.on("pdfParser_dataReady", pdfData => {
    fs.writeFileSync("e:/FC Technology/MeuPrazoJus/Auditoria/Auditoria_AdSense_MeuPrazoJus.txt", pdfParser.getRawTextContent());
    console.log("PDF text extracted successfully with pdf2json.");
});

pdfParser.loadPDF("e:/FC Technology/MeuPrazoJus/Auditoria/Auditoria_AdSense_MeuPrazoJus.pdf");
