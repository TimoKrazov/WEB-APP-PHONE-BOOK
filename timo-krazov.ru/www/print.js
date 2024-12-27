function printDiv() {   
    var contentPrint = document.getElementById('content').innerHTML;
    var newWindow = window.open('', '', 'width=800, height=600');
    newWindow.document.write(`
                            <html>
                                <head>
                                    <title>Отчёт</title>
                                    <link rel="stylesheet" type="text/css" href="my-style.css"/>
                                </head>
                                <body>
                                    ${contentPrint}
                                </body>
                            <html>
                            `);
    newWindow.document.close;
    setTimeout(function () {
        newWindow.print();
        newWindow.close();
    }, 500);
    // newWindow.onload = function() {
    //     newWindow.print();
    //     newWindow.close();
    // }
    // newWindow.print();
    // newWindow.close();
}