function callSaveEndpoint(url) {

    const currentUrl = new URL(url, window.location.origin);

    const images = document.getElementsByTagName('img');
    for (let i = 0; i < images.length; i++) {
        if (images[i].alt === 'Generated_image') {
            currentUrl.searchParams.delete("urlDownload");
            currentUrl.searchParams.set("urlDownload", encodeURI(images[i].src));
            currentUrl.searchParams.delete("methodDesired");
            currentUrl.searchParams.set("methodDesired", "downloadImage");
            url = currentUrl.toString();
            console.log("urlDownload:", images[i].src)
        }
    }

    fetch(url, {
        method: 'GET',
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error on server side');
            }
            console.log("response from server", response);
            return response.blob();
        })
        .then(blob => {

            const urlBlob = window.URL.createObjectURL(blob);
            console.log("urlBlob:", urlBlob);
            const a = document.createElement('a');
            a.href = urlBlob;
            a.download = "AIPic.png";
            document.body.appendChild(a);
            a.click();

            window.URL.revokeObjectURL(urlBlob);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
