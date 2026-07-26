<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe suggestions</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css','resources/js/app.js'])
    @endif
    <style>
        body {
            min-height: 100vh;
            background-color: #f8fafc;
            color: #334155;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
        }

        .page {
            max-width: 80rem;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header */
        .header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .header-eyebrow {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            color: #f97316;
            margin: 0;
        }

        .header-title {
            margin-top: 0.5rem;
            font-size: 1.875rem;
            font-weight: 600;
        }

        .header-subtitle {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .header-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .badge-success {
            border-radius: 9999px;
            background: #d1fae5;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #047857;
        }

        .btn-logout {
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            background: #fff;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .btn-logout:hover {
            background: #f1f5f9;
        }

        /* Layout */
        .layout {
            margin-top: 2rem;
            display: grid;
            gap: 1.5rem;
        }

        @media (min-width: 1024px) {
            .layout {
                grid-template-columns: 0.95fr 1.05fr;
            }
        }

        .card {
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .card-subtitle {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .form-stack {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label.field-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        textarea, .ocr-textarea {
            width: 100%;
            box-sizing: border-box;
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            font-size: 0.875rem;
            outline: none;
        }
        textarea:focus {
            border-color: #fb923c;
            box-shadow: 0 0 0 2px #fed7aa;
        }

        .tool-grid {
            display: grid;
            gap: 1rem;
        }
        @media (min-width: 1024px) {
            .tool-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .tool-card {
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 1rem;
        }

        .tool-card--accent {
            border: 1px solid #ffedd5;
            background: #fff7ed;
        }

        .tool-card h3 {
            font-size: 0.875rem;
            font-weight: 600;
            margin: 0;
        }
        .tool-card--accent h3 {
            color: #c2410c;
        }

        .tool-card p {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        .tool-card--accent p {
            color: #c2410c;
        }

        .file-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .file-label {
            flex: 1;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        .file-label span {
            color: #64748b;
        }
        .file-label input {
            margin-top: 0.5rem;
            width: 100%;
        }

        .btn-row {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 1rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .btn-dark { background: #0f172a; }
        .btn-dark:hover { background: #1e293b; }

        .btn-red { background: #e11d48; }
        .btn-red:hover { background: #be123c; }

        .btn-green { background: #059669; }
        .btn-green:hover { background: #047857; }

        .btn-orange { background: #f97316; }
        .btn-orange:hover { background: #ea580c; }

        .hidden { display: none; }

        .preview-label {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0;
        }

        canvas#captureCanvas {
            margin-top: 0.5rem;
            width: 100%;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }

        .btn-status-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }

        .status-text {
            font-size: 0.875rem;
            color: #64748b;
        }
        .status-text--accent {
            color: #c2410c;
        }

        .submit-btn {
            width: 100%;
            border: none;
            border-radius: 1.5rem;
            background: #0f172a;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .submit-btn:hover { background: #1e293b; }

        /* Recipe list */
        .recipe-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .recipe-list {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .recipe-item {
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .recipe-header {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .recipe-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }
        }

        .recipe-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .recipe-description {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #475569;
        }

        .match-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            background: #d1fae5;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #047857;
            white-space: nowrap;
        }

        .ingredient-tags {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .ingredient-tag {
            border-radius: 9999px;
            background: #fff;
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #334155;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div>
                <p class="header-eyebrow">WhatToCook</p>
                <h1 class="header-title">Recipe suggestions</h1>
                <p class="header-subtitle">Scan ingredients, use your voice, and find recipes from your pantry.</p>
            </div>
            <div class="header-actions">
                <span class="badge-success">Logged in</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>
        </div>

        <div class="layout">
            <div class="recipe-panel">
                <div class="card">
                    <h2 class="card-title">Tell us what you have</h2>
                    <p class="card-subtitle">Enter ingredients manually or use OCR and voice input.</p>

                    <form method="GET" action="{{ route('dashboard') }}" class="form-stack">
                        <div>
                            <label class="field-label">Available ingredients</label>
                            <textarea name="ingredients" rows="5" placeholder="Example: tomato, pasta, basil, chicken">{{ $selectedIngredients ?? '' }}</textarea>
                        </div>

                        <div class="tool-grid">
                            <div class="tool-card">
                                <h3>OCR scanner</h3>
                                <p>Upload a label photo or capture from your camera.</p>
                                <div class="form-stack">
                                    <div class="file-row">
                                        <label class="file-label">
                                            <span>Image</span>
                                            <input id="imageInput" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <div class="btn-row">
                                        <button type="button" id="cameraButton" class="btn btn-dark">Use camera</button>
                                        <button type="button" id="stopCameraButton" class="btn btn-red hidden">Stop</button>
                                    </div>
                                    <div id="previewContainer" class="hidden">
                                        <p class="preview-label">Preview</p>
                                        <canvas id="captureCanvas"></canvas>
                                    </div>
                                    <div class="btn-status-row">
                                        <button type="button" id="runOcrButton" class="btn btn-green">Scan image</button>
                                        <button type="button" id="insertOcrButton" class="btn btn-orange">Insert</button>
                                        <span id="ocrProgress" class="status-text">Ready</span>
                                    </div>
                                    <textarea id="ocrResult" rows="4" class="ocr-textarea" placeholder="OCR results will appear here"></textarea>
                                </div>
                            </div>

                            <div class="tool-card tool-card--accent">
                                <h3>Voice input</h3>
                                <p>Speak your pantry ingredients instead of typing.</p>
                                <button type="button" id="voiceButton" class="btn btn-orange" style="margin-top: 1rem;">Start listening</button>
                                <p id="voiceStatus" class="status-text status-text--accent" style="margin-top: 0.75rem;">Tap Start to speak your ingredients.</p>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">Find recipes</button>
                    </form>
                </div>
            </div>

            <div class="recipe-panel">
                <div class="card">
                    <h2 class="card-title">Suggested recipes</h2>
                    <p class="card-subtitle">Recipes are ranked by how many ingredients match your list.</p>
                    <div class="recipe-list">
                        @foreach ($recipes as $recipe)
                            <div class="recipe-item">
                                <div class="recipe-header">
                                    <div>
                                        <h3 class="recipe-title">{{ $recipe['title'] }}</h3>
                                        <p class="recipe-description">{{ $recipe['description'] }}</p>
                                    </div>
                                    <span class="match-badge">{{ $recipe['match_count'] }} matches</span>
                                </div>
                                <div class="ingredient-tags">
                                    @foreach ($recipe['ingredients'] as $ingredient)
                                        <span class="ingredient-tag">{{ ucfirst($ingredient) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.5/dist/tesseract.min.js"></script>
    <script>
        // OCR / Camera integration
        const imageInput = document.getElementById('imageInput');
        const cameraButton = document.getElementById('cameraButton');
        const stopCameraButton = document.getElementById('stopCameraButton');
        const previewContainer = document.getElementById('previewContainer');
        const captureCanvas = document.getElementById('captureCanvas');
        const runOcrButton = document.getElementById('runOcrButton');
        const ocrResult = document.getElementById('ocrResult');
        const ocrProgress = document.getElementById('ocrProgress');
        const insertOcrButton = document.getElementById('insertOcrButton');
        const ingredientsField = document.querySelector('textarea[name="ingredients"]');

        let videoStream = null;
        let videoEl = null;

        function showPreviewFromImage(img) {
            const ctx = captureCanvas.getContext('2d');
            captureCanvas.width = img.width;
            captureCanvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            previewContainer.classList.remove('hidden');
        }

        imageInput.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => {
                showPreviewFromImage(img);
                URL.revokeObjectURL(url);
            };
            img.src = url;
        });

        cameraButton.addEventListener('click', async () => {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Camera not supported in this browser.');
                return;
            }

            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                videoEl = document.createElement('video');
                videoEl.autoplay = true;
                videoEl.playsInline = true;
                videoEl.srcObject = videoStream;
                videoEl.style.display = 'none';
                document.body.appendChild(videoEl);

                // Wait for video to be ready then draw first frame to canvas
                videoEl.addEventListener('loadeddata', () => {
                    const w = videoEl.videoWidth;
                    const h = videoEl.videoHeight;
                    captureCanvas.width = w;
                    captureCanvas.height = h;
                    const ctx = captureCanvas.getContext('2d');
                    ctx.drawImage(videoEl, 0, 0, w, h);
                    previewContainer.classList.remove('hidden');
                });

                cameraButton.classList.add('hidden');
                stopCameraButton.classList.remove('hidden');
            } catch (err) {
                console.error(err);
                alert('Could not access camera.');
            }
        });

        stopCameraButton.addEventListener('click', () => {
            if (videoStream) {
                videoStream.getTracks().forEach(t => t.stop());
                videoStream = null;
            }
            if (videoEl) {
                videoEl.remove();
                videoEl = null;
            }
            cameraButton.classList.remove('hidden');
            stopCameraButton.classList.add('hidden');
        });

        runOcrButton.addEventListener('click', async () => {
            const dataUrl = captureCanvas.toDataURL('image/png');
            ocrProgress.textContent = 'Recognizing...';

            try {
                const worker = Tesseract.createWorker({
                    logger: m => {
                        if (m && m.status && m.progress != null) {
                            const percent = Math.round(m.progress * 100);
                            ocrProgress.textContent = `${m.status} (${percent}%)`;
                        }
                    }
                });

                await worker.load();
                await worker.loadLanguage('eng');
                await worker.initialize('eng');
                const { data: { text } } = await worker.recognize(dataUrl);
                ocrResult.value = text.trim();
                await worker.terminate();
                ocrProgress.textContent = 'Done';
            } catch (err) {
                console.error(err);
                ocrProgress.textContent = 'OCR failed';
            }
        });

        insertOcrButton.addEventListener('click', () => {
            const text = ocrResult.value.trim();
            if (!text) return;
            // Normalize line breaks and commas into a comma-separated list
            const normalized = text.replace(/\r\n?/g, '\n').split('\n').map(s => s.trim()).filter(Boolean).join(', ');
            if (ingredientsField.value) {
                ingredientsField.value += ', ' + normalized;
            } else {
                ingredientsField.value = normalized;
            }
        });
    </script>

    <script>
        const voiceButton = document.getElementById('voiceButton');
        const voiceStatus = document.getElementById('voiceStatus');

        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';

            voiceButton.addEventListener('click', () => {
                voiceStatus.textContent = 'Listening...';
                recognition.start();
            });

            recognition.onresult = (event) => {
                const transcript = Array.from(event.results).map(result => result[0].transcript).join(' ');
                if (ingredientsField.value) {
                    ingredientsField.value += ', ' + transcript;
                } else {
                    ingredientsField.value = transcript;
                }
                voiceStatus.textContent = `Captured: ${transcript}`;
            };

            recognition.onerror = () => {
                voiceStatus.textContent = 'Voice input could not be started.';
            };
        } else {
            voiceButton.disabled = true;
            voiceButton.textContent = 'Unavailable';
            voiceStatus.textContent = 'Speech recognition is not supported in this browser.';
        }
    </script>
</body>
</html>