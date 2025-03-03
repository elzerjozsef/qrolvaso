<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Kód Teszt</title>
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <style>
        /* Törlés gomb stílusa, ha aktív */
        #delete-button.active {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <h1>QR Kód Olvasás Teszt</h1>

    <!-- Kamera kiválasztása -->
    <label for="camera-select">Válassz kamerát:</label>
    <select id="camera-select"></select>

    <!-- Törlés gomb -->
    <button id="delete-button" onclick="toggleDeleteMode()">Törlés Mód</button>

    <div id="qr-reader" style="width:300px; margin-top:20px;"></div>
    <button onclick="startQRScanner()">QR Kód Beolvasása</button>

    <div id="qr-code-info" style="margin-top:20px; font-size:18px;"></div>
    
    <script>
        let qrCodeReader = null;
        let deleteMode = false;  // Törlés mód alapértelmezett állapot

        // Kamerák listájának betöltése
        function loadCameras() {
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    const cameraSelect = document.getElementById('camera-select');
                    cameraSelect.innerHTML = '';  // Legördülő menü ürítése

                    devices.forEach((device, index) => {
                        const option = document.createElement('option');
                        option.value = device.id;
                        option.text = device.label || `Camera ${index + 1}`;
                        cameraSelect.appendChild(option);
                    });
                } else {
                    alert("Nincs elérhető kamera.");
                }
            }).catch(err => {
                console.error("Kamera hozzáférési hiba:", err);
                alert("Hiba történt a kamera elérésénél.");
            });
        }

        // QR olvasó indítása
        function startQRScanner() {
            const selectedCameraId = document.getElementById('camera-select').value;

            if (qrCodeReader) {
                qrCodeReader.stop().then(() => {
                    qrCodeReader = null;
                    startQrCodeReader(selectedCameraId);
                }).catch(err => {
                    console.error("QR-kód olvasó leállítási hiba:", err);
                });
            } else {
                startQrCodeReader(selectedCameraId);
            }
        }

        // QR-kód olvasó inicializálása
        function startQrCodeReader(cameraId) {
            document.getElementById("qr-reader").style.display = "block";
            qrCodeReader = new Html5Qrcode("qr-reader");
            qrCodeReader.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText) => {
                    console.log("QR kód olvasva:", decodedText);  // Naplózza a QR-kód értékét
                    displayQRCode(decodedText);  // Megjeleníti a QR-kódot
                    if (deleteMode) {
                        deleteQRCode(decodedText);  // QR törlése, ha törlés mód aktív
                    }
                },
                (errorMessage) => {
                    console.warn("QR-kód olvasási hiba:", errorMessage);
                }
            ).catch(err => {
                console.error("QR-kód olvasó indítási hiba:", err);
                alert("Nem sikerült elindítani a QR-kód olvasót.");
            });
        }

        // QR kód információ megjelenítése a felhasználónak
        function displayQRCode(qrCode) {
            const infoDiv = document.getElementById("qr-code-info");
            infoDiv.innerHTML = `Olvasott QR-kód: <strong>${qrCode}</strong>`;
        }

        // Törlés mód be- és kikapcsolása
        function toggleDeleteMode() {
            deleteMode = !deleteMode;
            const deleteButton = document.getElementById('delete-button');
            deleteButton.classList.toggle("active", deleteMode);
            deleteButton.innerText = deleteMode ? "Törlés Mód Aktív" : "Törlés Mód";
            if (deleteMode) {
                startQRScanner();  // Automatikusan indítsa el a QR olvasót törléshez
            } else {
                stopQRScanner();
            }
        }

        // QR kód törlése az Excel fájlból
        function deleteQRCode(qrCode) {
            console.log("QR kód törlés: ", qrCode);  // Ellenőrizd, hogy a QR-kód érték megfelelően érkezik-e
            $.post("delete_qr_code.php", { qr_code: qrCode }, function(response) {
                alert(response);
            }).fail(function(error) {
                console.error("Hiba történt a QR kód törlésénél:", error);
            });
        }

        // QR Olvasó leállítása
        function stopQRScanner() {
            if (qrCodeReader) {
                qrCodeReader.stop().then(() => {
                    qrCodeReader = null;
                }).catch(err => {
                    console.error("QR-kód olvasó leállítási hiba:", err);
                });
            }
        }

        // Kamerák betöltése az oldal betöltésekor
        window.onload = loadCameras;
    </script>
</body>
</html>
