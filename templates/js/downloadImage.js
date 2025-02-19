function downloadImage(imageUrl, fileName = "imagen") {
    console.log("Descargando imagen:", imageUrl);
    fetch(imageUrl, { mode: "no-cors" })
        .then(response => response.blob())
        .then(blob => {
            const imageUrl = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = imageUrl;
            a.download = "imagen.png";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        })
        .catch(error => console.error("Error descargando la imagen:", error));
}