function callSaveEndpoint(url) {

    const currentUrl = new URL(url, window.location.origin);

    const images = document.getElementsByTagName('img');
    for (let i = 0; i < images.length; i++) {
        if (images[i].alt === 'Generated_image') {
            if (images[i].src.startsWith('blob:') || images[i].src.startsWith('data:')) {
                const a = document.createElement('a');
                a.href = images[i].src;
                a.download = 'AIPic.png';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                return;
            }

            currentUrl.searchParams.delete("urlDownload");
            currentUrl.searchParams.set("urlDownload", encodeURI(images[i].src));
            currentUrl.searchParams.delete("methodDesired");
            currentUrl.searchParams.set("methodDesired", "downloadImage");
            url = currentUrl.toString();

        }
    }
    fetch(url, {
        method: 'GET',
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error on server side');
            }

            return response.blob();
        })
        .then(blob => {

            const urlBlob = window.URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = urlBlob;
            a.download = "AIPic.png";
            document.body.appendChild(a);
            a.click();

            window.URL.revokeObjectURL(urlBlob);
        })
        .catch(error => {

        });
}
