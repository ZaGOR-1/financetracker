<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒 Security Test - Finance Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-4xl font-bold text-gray-900">🔒 Security Test</h1>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">← Dashboard</a>
            </div>
            <p class="text-gray-600 mb-6">Перевірка Security Headers через Laravel route</p>
            
            <!-- Real-time Score -->
            <div class="text-center bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-8 text-white">
                <p class="text-sm opacity-90 mb-2">Security Score</p>
                <p id="scoreValue" class="text-7xl font-bold">?</p>
                <p id="scoreGrade" class="text-2xl font-bold mt-2">Завантаження...</p>
            </div>
        </div>

        <!-- Warning for static files -->
        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-6 mb-6">
            <div class="flex items-start space-x-3">
                <span class="text-3xl">⚠️</span>
                <div>
                    <h3 class="font-bold text-yellow-800 mb-2">ВАЖЛИВО!</h3>
                    <p class="text-yellow-700 mb-2">
                        Статичні HTML файли в <code class="bg-yellow-200 px-2 py-1 rounded">/public/</code> 
                        <strong>НЕ проходять через Laravel middleware</strong> і не мають Security Headers!
                    </p>
                    <div class="mt-3 space-y-1 text-sm">
                        <p>❌ <code class="bg-yellow-200 px-1 rounded">/test-security.html</code> - БЕЗ захисту (статичний)</p>
                        <p>✅ <code class="bg-green-200 px-1 rounded">/test-security</code> - З захистом (Laravel route)</p>
                        <p>✅ <code class="bg-green-200 px-1 rounded">/dashboard</code> - З захистом (Laravel)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Headers -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🛡️ Security Headers Check</h2>
            <div id="headersCheck" class="space-y-3">
                <p class="text-gray-500">Аналіз...</p>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">📋 Як перевірити Headers вручну</h2>
            <ol class="space-y-3 text-gray-700">
                <li class="flex items-start space-x-2">
                    <span class="font-bold text-blue-600">1.</span>
                    <div>
                        <strong>Відкрийте DevTools:</strong> Натисніть F12 або правий клік → Inspect
                    </div>
                </li>
                <li class="flex items-start space-x-2">
                    <span class="font-bold text-blue-600">2.</span>
                    <div>
                        <strong>Перейдіть на вкладку Network</strong>
                    </div>
                </li>
                <li class="flex items-start space-x-2">
                    <span class="font-bold text-blue-600">3.</span>
                    <div>
                        <strong>Перезавантажте сторінку:</strong> Ctrl+R або F5
                    </div>
                </li>
                <li class="flex items-start space-x-2">
                    <span class="font-bold text-blue-600">4.</span>
                    <div>
                        <strong>Виберіть перший запит</strong> (test-security або localhost)
                    </div>
                </li>
                <li class="flex items-start space-x-2">
                    <span class="font-bold text-blue-600">5.</span>
                    <div>
                        <strong>Перегляньте Headers → Response Headers</strong>
                        <div class="mt-2 bg-gray-50 p-3 rounded font-mono text-xs space-y-1">
                            <div>✅ x-frame-options: SAMEORIGIN</div>
                            <div>✅ x-content-type-options: nosniff</div>
                            <div>✅ x-xss-protection: 1; mode=block</div>
                            <div>✅ referrer-policy: strict-origin-when-cross-origin</div>
                            <div>✅ permissions-policy: geolocation=()...</div>
                            <div>✅ content-security-policy: default-src 'self'...</div>
                        </div>
                    </div>
                </li>
            </ol>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🔗 Тестові сторінки</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/test-security" class="block p-4 border-2 border-green-500 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <h3 class="font-bold text-green-700">✅ /test-security</h3>
                    <p class="text-sm text-green-600 mt-1">Laravel route (ВІ захист)</p>
                </a>
                
                <a href="/dashboard" class="block p-4 border-2 border-blue-500 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <h3 class="font-bold text-blue-700">✅ /dashboard</h3>
                    <p class="text-sm text-blue-600 mt-1">Laravel route (З захистом)</p>
                </a>
                
                <a href="/test-security.html" class="block p-4 border-2 border-red-500 bg-red-50 rounded-lg hover:bg-red-100 transition">
                    <h3 class="font-bold text-red-700">❌ /test-security.html</h3>
                    <p class="text-sm text-red-600 mt-1">Static HTML (БЕЗ захисту)</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Перевірка Security Headers
        async function checkSecurityHeaders() {
            try {
                const response = await fetch(window.location.href, {
                    method: 'GET',
                    cache: 'no-cache'
                });
                
                const headers = {
                    'X-Frame-Options': response.headers.get('x-frame-options'),
                    'X-Content-Type-Options': response.headers.get('x-content-type-options'),
                    'X-XSS-Protection': response.headers.get('x-xss-protection'),
                    'Referrer-Policy': response.headers.get('referrer-policy'),
                    'Permissions-Policy': response.headers.get('permissions-policy'),
                    'Content-Security-Policy': response.headers.get('content-security-policy'),
                    'Strict-Transport-Security': response.headers.get('strict-transport-security'),
                };

                let score = 0;
                const maxScore = 7;
                let html = '';

                // Перевірка кожного header
                for (const [name, value] of Object.entries(headers)) {
                    const hasValue = value !== null && value !== '';
                    if (hasValue) score++;
                    
                    const statusColor = hasValue ? 'green' : 'red';
                    const statusIcon = hasValue ? '✅' : '❌';
                    const statusText = hasValue ? 'Активний' : 'Відсутній';
                    const displayValue = value ? (value.length > 80 ? value.substring(0, 80) + '...' : value) : 'N/A';
                    
                    html += `
                        <div class="flex items-start space-x-3 p-4 bg-${statusColor}-50 border-2 border-${statusColor}-200 rounded-lg">
                            <span class="text-2xl">${statusIcon}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-bold text-${statusColor}-800">${name}</p>
                                    <span class="text-xs font-bold text-${statusColor}-700 uppercase">${statusText}</span>
                                </div>
                                <p class="text-sm text-${statusColor}-700 font-mono break-all">${displayValue}</p>
                            </div>
                        </div>
                    `;
                }

                document.getElementById('headersCheck').innerHTML = html;

                // Security Score
                const percentage = Math.round((score / maxScore) * 100);
                let grade, gradeColor;
                
                if (percentage >= 90) {
                    grade = 'A+';
                    gradeColor = 'green';
                } else if (percentage >= 80) {
                    grade = 'A';
                    gradeColor = 'green';
                } else if (percentage >= 70) {
                    grade = 'B';
                    gradeColor = 'yellow';
                } else if (percentage >= 60) {
                    grade = 'C';
                    gradeColor = 'orange';
                } else {
                    grade = 'F';
                    gradeColor = 'red';
                }

                document.getElementById('scoreValue').textContent = `${score}/${maxScore}`;
                document.getElementById('scoreGrade').innerHTML = `
                    Grade: <span class="text-${gradeColor}-300">${grade}</span> 
                    <span class="text-sm opacity-75">(${percentage}%)</span>
                `;

                // Змінюємо колір фону score в залежності від результату
                const scoreContainer = document.getElementById('scoreValue').parentElement;
                if (percentage >= 80) {
                    scoreContainer.className = 'text-center bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-8 text-white';
                } else if (percentage >= 60) {
                    scoreContainer.className = 'text-center bg-gradient-to-r from-yellow-500 to-orange-600 rounded-xl p-8 text-white';
                } else {
                    scoreContainer.className = 'text-center bg-gradient-to-r from-red-500 to-pink-600 rounded-xl p-8 text-white';
                }

            } catch (error) {
                console.error('Помилка перевірки headers:', error);
                document.getElementById('headersCheck').innerHTML = `
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                        <p class="text-red-800 font-bold">❌ Помилка перевірки</p>
                        <p class="text-red-700 text-sm mt-1">${error.message}</p>
                    </div>
                `;
            }
        }

        // Запускаємо перевірку після завантаження
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkSecurityHeaders);
        } else {
            checkSecurityHeaders();
        }
    </script>
</body>
</html>
