<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tolmácsvevő regisztráció</title>
    <!-- Helyi JS fájl a QR kód olvasáshoz -->
    <script>
        // JavaScript kód a cím módosításához
        window.onload = function() {
            document.title = "dd";  // Itt módosítod a címet
        }
    </script>
    <script src="js/html5-qrcode.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Linkeld be a külső stíluslapot -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
   <h1>Tolmácsvevő regisztráció</h1>
    <p>Regisztrálja a vevőket a résztvevőkhöz.</p>

    <script>
        // JavaScript kód a cím módosításához
        document.title = "Új cím szöveg";  // Itt módosítod a címet
    </script>

   
    <!-- Kamera kiválasztása -->
    <label for="camera-select">Válassz kamerát:</label>
    <select id="camera-select" onchange="startQRScanner()"></select>
 
 <div id="qr-reader" style="width:300px; margin-top:20px;"></div>
    <div id="qr-code-info" style="margin-top:20px; font-size:18px;"></div>
 
     <!-- Keresőmező -->
    <input type="text" id="search" placeholder="Név keresése..." oninput="searchName()">
    <ul id="result"></ul>
<!-- Modális Popup ablak -->
<div id="popup-modal" style="display: none;">
    <div id="popup-content">
        <h3 id="popup-message"></h3>
        <button onclick="confirmSelection()">OK</button>
        <button onclick="cancelSelection()">Mégse</button>
    </div>
</div>


<!-- Név hozzáadása -->
    <h2>Új név hozzáadása</h2>
    <input type="text" id="new-name" placeholder="Írd be a hozzáadni kívánt nevet...">
    <button onclick="addName()">Név hozzáadása</button>



    <!-- Törlés gomb -->
    <a href="delindex.php">
        <button id="delete-button">Törlés Mód</button>
    </a>

    

    

    <script>
        let selectedName = null;
        let qrCodeReader = null;
        let deleteMode = false;  // Törlés mód alapértelmezés szerint inaktív

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
        // Név hozzáadása
function addName() {
    const name = document.getElementById('new-name').value;
    if (name) {
        $.post("add_name.php", { name: name }, function(response) {
            console.log("Válasz a név hozzáadásáról: ", response);

            // Válasz feldolgozása
            try {
                const res = JSON.parse(response); // A válasz JSON formátumban van
                if (res.status === 'success') {
                    alert("Sikeresen hozzáadva a név: " + name);
                    document.getElementById('new-name').value = '';  // A mező kiürítése
                } else {
                    alert("Hiba történt: " + res.message);  // Hiba üzenet
                }
            } catch (error) {
                console.error("JSON parse hiba:", error);
                alert("Hiba történt a válasz feldolgozásakor.");
            }
        }).fail(function(error) {
            console.error("Hiba történt a név hozzáadásánál:", error);
            alert("Hiba történt a név hozzáadásánál.");
        });
    } else {
        alert("Kérlek, adj meg egy nevet!");
    }
}


        // QR kód törlése az Excel fájlból
function deleteQRCode(qrCode) {
    console.log("QR kód törlés: ", qrCode);  // Ellenőrizd, hogy a QR-kód érték megfelelően érkezik-e

    $.post("delete_qr_code.php", { qr_code: qrCode }, function(response) {
        console.log("Válasz a törlés után: ", response);  // Válasz naplózása

        try {
            const res = JSON.parse(response); // A válasz JSON formátumban van
            if (res.status === 'success') {
                alert("Törölve: " + res.deletedQR);  // Megjelenítjük a törölt QR-kódot
            } else {
                alert("Hiba történt: " + res.message);
            }
        } catch (error) {
            console.error("JSON parse hiba:", error);
            alert("Hiba történt a válasz feldolgozásakor.");
        }
    }).fail(function(error) {
        console.error("Hiba történt a QR kód törlésénél:", error);
        alert("Hiba történt a törlés során.");
    });
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


        // Keresés a nevek között
        function searchName() {
            let searchTerm = $("#search").val();
            $.post("search_name.php", { term: searchTerm }, function(data) {
                $("#result").html(data);
            });
        }

        // Név kiválasztása és QR olvasó automatikus megnyitása
function selectName(name) {
    if (deleteMode) return;  // Ha törlés mód aktív, ne lehessen nevet választani
    
    selectedName = name;
    showPopup("Kiválasztott név: " + name);  // Megjelenítjük a modális ablakot
}

// Modális ablak megjelenítése
function showPopup(message) {
    document.getElementById('popup-message').innerText = message;
    document.getElementById('popup-modal').style.display = "block";  // Popup megjelenítése
}

// OK gomb funkció
function confirmSelection() {
    alert("A név " + selectedName + " sikeresen kiválasztva!");
    document.getElementById('popup-modal').style.display = "none";  // Popup bezárása
    startQRScanner();  // Elindítjuk a QR-kód olvasókat
}

// Mégse gomb funkció
function cancelSelection() {
    selectedName = null;  // Eltávolítjuk a kiválasztott nevet
    document.getElementById('popup-modal').style.display = "none";  // Popup bezárása
}


        // QR-kód olvasó indítása a kiválasztott kamerával
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
                    if (deleteMode) {
                        deleteQRCode(decodedText);
                    } else {
                        handleQRCode(decodedText);
                    }
                    if (!deleteMode) stopQRScanner();  // Törlés után leállítjuk az olvasót
                },
                (errorMessage) => {
                    console.warn("QR-kód olvasási hiba:", errorMessage);
                }
            ).catch(err => {
                console.error("QR-kód olvasó indítási hiba:", err);
                alert("Nem sikerült elindítani a QR-kód olvasót.");
            });
        }

        // QR kód értékének kezelése és mentése az Excel fájlba
        function handleQRCode(decodedText) {
            if (selectedName) {
                $.post("update_qr_code.php", { name: selectedName, qr_code: decodedText }, function(response) {
                    alert(response);
                    selectedName = null;
                }).fail(function(error) {
                    console.error("Hiba történt a QR kód hozzáadása közben:", error);
                });
            } else {
                alert("Nincs kiválasztott név a QR-kód hozzárendeléséhez.");
            }
        }

        // Törlés mód be- és kikapcsolása
        function toggleDeleteMode() {
            deleteMode = !deleteMode;
            const deleteButton = document.getElementById('delete-button');
            deleteButton.classList.toggle("active", deleteMode);
            deleteButton.innerText = deleteMode ? "Törlés Mód Aktív" : "Törlés Mód";
            if (deleteMode) {
                startQRScanner();
            } else {
                stopQRScanner();
            }
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
