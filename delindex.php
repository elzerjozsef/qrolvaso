<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Kód Törlés</title>
    <script src="js/html5-qrcode.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Linkeld be a külső stíluslapot -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>QR Kód Törlés</h1>

    <!-- Kamera kiválasztása -->
    <label for="camera-select">Válassz kamerát:</label>
    <select id="camera-select" onchange="startQRScanner()"></select>

    <div id="qr-reader" style="width:300px; margin-top:20px;"></div>
    <div id="qr-code-info" style="margin-top:20px; font-size:18px;"></div>

    <script>
        let qrCodeReader = null;
        let deleteMode = true;  // Törlés mód alapértelmezés szerint aktív
        let isProcessing = false; // Segítség, hogy ne történjen több olvasás egyszerre

        // Kamerák betöltése
        function loadCameras() {
            Html5Qrcode.getCameras().then(devices => {
                const cameraSelect = document.getElementById('camera-select');
                cameraSelect.innerHTML = '';  // Ürítjük a legördülő menüt

                if (devices && devices.length) {
                    let rearCamera = null;
                    let frontCamera = null;

                    // Kamerák szétválasztása hátsó és elülső kamerára
                    devices.forEach((device, index) => {
                        if (device.label.toLowerCase().includes("back")) {
                            rearCamera = device;
                        } else if (device.label.toLowerCase().includes("front")) {
                            frontCamera = device;
                        }
                        
                        const option = document.createElement('option');
                        option.value = device.id;
                        option.text = device.label || `Camera ${index + 1}`;
                        cameraSelect.appendChild(option);
                    });

                    // Ha van hátsó kamera, azt állítjuk be alapértelmezettként
                    if (rearCamera) {
                        cameraSelect.value = rearCamera.id;
                    } else if (frontCamera) {
                        cameraSelect.value = frontCamera.id;
                    } else {
                        cameraSelect.value = devices[0].id; // Ha nincs hátsó vagy elülső, az elsőt választjuk
                    }

                    startQRScanner();  // Kamera választása után elindítjuk az olvasót
                } else {
                    alert("Nincs elérhető kamera.");
                }
            }).catch(err => {
                console.error("Kamera hozzáférési hiba:", err);
                alert("Hiba történt a kamera elérésénél.");
            });
        }

        // QR-kód olvasó indítása
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

        // QR-kód olvasó inicializálása a megadott kamerával
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
                    console.log("QR kód olvasva:", decodedText);
                    if (!isProcessing) { // Ne próbáljunk újra olvasni, ha már folyamatban van a törlés
                        isProcessing = true; // Jelzés, hogy épp törlünk
                        deleteQRCode(decodedText); // QR-kód törlése
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

        // QR kód törlése az Excel fájlból
function deleteQRCode(qrCode) {
    console.log("QR kód törlés: ", qrCode);

    $.post("delete_qr_code.php", { qr_code: qrCode }, function(response) {
        console.log("Válasz a törlés után: ", response);

        let res;
        try {
            res = JSON.parse(response);
        } catch (error) {
            console.error("JSON parse hiba:", error);
            alert("Hiba történt a válasz feldolgozásakor.");
            return;
        }

        if (res.status === 'success') {
            showDeleteMessage("Törölve: " + res.deletedQR); // Törlés sikeres
        } else {
            showDeleteMessage("Hiba történt: " + res.message); // Hiba a törlés során
        }

        // Miután a törlés megtörtént, újraindítjuk az olvasást
        setTimeout(() => {
            isProcessing = false; // A folyamat befejeződött, újra indíthatjuk a következő olvasást
        }, 2000); // 2 másodperces késleltetés, hogy ne történjen újraolvasás azonnal
    }).fail(function(error) {
        console.error("Hiba történt a QR kód törlésénél:", error);
        showDeleteMessage("Hiba történt a törlés során.");
        setTimeout(() => {
            isProcessing = false;
        }, 2000);
    });
}

// Üzenet megjelenítése és automatikus eltüntetése 1.5 mp után
function showDeleteMessage(message) {
    const messageDiv = document.createElement('div');
    messageDiv.id = 'delete-message';
    messageDiv.style.position = 'fixed';
    messageDiv.style.top = '20px';
    messageDiv.style.left = '50%';
    messageDiv.style.transform = 'translateX(-50%)';
    messageDiv.style.backgroundColor = '#4CAF50';
    messageDiv.style.color = 'white';
    messageDiv.style.padding = '10px 20px';
    messageDiv.style.borderRadius = '5px';
    messageDiv.style.fontSize = '16px';
    messageDiv.style.zIndex = '1001';
    messageDiv.textContent = message;

    document.body.appendChild(messageDiv);

    // 1.5 másodperc után eltüntetjük az üzenetet
    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s ease-out';
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            messageDiv.remove();
        }, 500); // 500ms késleltetés a törléshez
    }, 1500);
}


        // QR Olvasó leállítása
        function stopQRScanner() {
            if (qrCodeReader) {
                qrCodeReader.stop().then(() => {
                    qrCodeReader = null;
                    console.log("QR-kód olvasó leállítva.");
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
