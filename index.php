<?php
/**
 * Kamus Digital PHP - Chips UI Edition (Optimized)
 * Fitur: Pilihan Sumber Multi-Kamus, Auto-Download file terpisah, Mode, Limit, Cookies.
 */

// Daftar Sumber Kamus
$kamusSources = [
    'kbbi' => [
        'name' => 'KBBI Lengkap v1',
        'url' => 'https://raw.githubusercontent.com/Kholbi/Kamus/refs/heads/main/WL01.txt',
        'filename' => 'kbbi_lengkap.txt' // Nama file berdasarkan judul
    ],
    'kbbi2' => [
        'name' => 'KBBI Lengkap v2',
        'url' => 'https://raw.githubusercontent.com/kholbi/Kamus/refs/heads/main/WL02.txt',
        'filename' => 'kbbi_lengkap2.txt' // Nama file berdasarkan judul
    ],
    'kbbi3' => [
        'name' => 'KBBI Lengkap v3',
        'url' => 'https://raw.githubusercontent.com/kholbi/Kamus/refs/heads/main/WL03%20-%20KBBI.txt',
        'filename' => 'kbbi_lengkap3.txt'
    ],
    'kbbi4' => [
        'name' => 'KBBI Lengkap vCrawl',
        'url' => 'https://raw.githubusercontent.com/kholbi/Kamus/refs/heads/main/WL04%20-%20Crawls.txt',
        'filename' => 'kbbi_lengkap4.txt'
    ],
    'kbbi5' => [
        'name' => 'KBBI Lengkap vIndoDict',
        'url' => 'https://raw.githubusercontent.com/kholbi/Kamus/refs/heads/main/WL05%20-%20IndoDict.txt',
        'filename' => 'kbbi_lengkap5.txt'
    ],
    'kbbi6' => [
        'name' => 'KBBI Lengkap vMySpell',
        'url' => 'https://raw.githubusercontent.com/kholbi/Kamus/refs/heads/main/WL06%20-%20MySpell.txt',
        'filename' => 'kbbi_lengkap6.txt'
    ],
    'kbbi7' => [
        'name' => 'KBBI Lengkap vIvanlanin',
        'url' => 'https://raw.githubusercontent.com/kholbi/Kamus/refs/heads/main/WL07%20-%20Ivanlanin.txt',
        'filename' => 'kbbi_lengkap7.txt'
    ]
];

if (isset($_GET['query'])) {
    header('Content-Type: application/json');
    $query = strtolower(trim($_GET['query']));

    // Parameter Filter & Mode
    $filterAlpha = isset($_GET['alpha']) && $_GET['alpha'] === 'true';
    $minLength = isset($_GET['minLen']) ? (int)$_GET['minLen'] : 1;
    $maxWords = isset($_GET['maxWords']) ? (int)$_GET['maxWords'] : 1;
    $searchMode = isset($_GET['mode']) ? $_GET['mode'] : 'start';

    // Pemilihan Sumber Kamus
    $sourceKey = isset($_GET['source']) && isset($kamusSources[$_GET['source']]) ? $_GET['source'] : 'kbbi';
    $activeSource = $kamusSources[$sourceKey];

    // Auto convert ekstensi apapun (.lst, .csv, dll) menjadi .txt saat disimpan
    $sourceFile = pathinfo($activeSource['filename'], PATHINFO_FILENAME) . '.txt';
    $remoteUrl = $activeSource['url'];

    // Fitur Auto-Download: Simpan berdasarkan judul/filename masing-masing
    if (!file_exists($sourceFile) && $remoteUrl !== '') {
        $options = ["http" => ["method" => "GET", "header" => "User-Agent: PHP\r\n"]];
        $context = stream_context_create($options);
        $content = @file_get_contents($remoteUrl, false, $context);

        if ($content !== false) {
            @file_put_contents($sourceFile, $content);
        }
    }

    // Limit pencarian (Default 50)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    if ($limit < 1) $limit = 50;
    if ($limit > 500) $limit = 500;

    $results = [];

    if ($query !== '' && file_exists($sourceFile)) {
        // Optimasi: Membaca file baris demi baris menggunakan fopen
        $handle = @fopen($sourceFile, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $word = trim($line);
                if ($word === '') continue;

                // 1. Filter: Maksimal jumlah kata (spasi)
                if (str_word_count($word) > $maxWords) continue;

                // 2. Filter: Minimal karakter
                if (strlen($word) < $minLength) continue;

                // 3. Filter: Hanya Alfabet (tanpa angka/simbol)
                if ($filterAlpha && !ctype_alpha(str_replace(' ', '', $word))) continue;

                // 4. Mode Pencarian
                $match = false;
                $wordLower = strtolower($word);

                if ($searchMode === 'start') {
                    $match = (strpos($wordLower, $query) === 0);
                } elseif ($searchMode === 'end') {
                    $queryLen = strlen($query);
                    $match = (substr_compare($wordLower, $query, -$queryLen) === 0);
                } elseif ($searchMode === 'contain') {
                    $match = (strpos($wordLower, $query) !== false);
                }

                if ($match) {
                    $results[] = $word;
                }

                // Batasi hasil maksimal sesuai limit
                if (count($results) >= $limit) break;
            }
            fclose($handle);
        }
    }

    echo json_encode($results);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamus Digital Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .search-bg { background-color: #0f172a; }
        .chip {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
        }
        .chip:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .chip:active {
            transform: translateY(0);
        }
        .filter-panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .filter-panel.open {
            max-height: 600px; /* Ditambah untuk mengakomodasi dropdown sumber */
        }
        /* Toast Animation */
        #toast {
            transition: opacity 0.3s, transform 0.3s;
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
        }
        #toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen relative pb-20">

    <!-- Header -->
    <header class="search-bg pt-12 pb-24 px-4 text-center">
        <h1 class="text-3xl font-bold text-white mb-2">Kamus Digital Kilat ⚡</h1>
        <p class="text-slate-400 text-sm">Cari awalan, akhiran, pilih sumber, atau copy kata instan.</p>
    </header>

    <main class="max-w-3xl mx-auto px-4 -mt-10">
        <!-- Search & Filter Area -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden relative z-10">
            <div class="p-2 flex items-center gap-2">
                <div class="relative flex-1 flex items-center">
                    <div class="pl-4 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Ketik kata untuk mencari..."
                        class="block w-full pl-3 pr-4 py-3 bg-transparent outline-none text-slate-700 text-lg"
                        autocomplete="off"
                    >
                </div>
                <!-- Toggle Filter -->
                <button id="filterBtn" class="p-3 text-slate-500 hover:bg-slate-100 rounded-xl transition-colors relative" title="Pengaturan Pencarian">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </button>
            </div>

            <!-- Filter Options Panel -->
            <div id="filterPanel" class="filter-panel bg-slate-50 border-t border-slate-100">
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <!-- Pilihan Sumber Data (Baru) -->
                    <div class="sm:col-span-2">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Sumber Kamus</span>
                        <select id="sourceFilter" class="w-full p-2.5 text-sm border border-slate-200 rounded-lg bg-white text-slate-700 focus:ring-2 focus:ring-indigo-200 outline-none cursor-pointer font-medium shadow-sm">
                            <?php foreach($kamusSources as $key => $src): ?>
                                <?php $safeFilename = pathinfo($src['filename'], PATHINFO_FILENAME) . '.txt'; ?>
                                <option value="<?= $key ?>"><?= $src['name'] ?> (<?= $safeFilename ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Mode Pencarian -->
                    <div class="sm:col-span-2 pt-2 border-t border-slate-200">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mode Pencarian</span>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="searchMode" value="start" class="peer sr-only" checked>
                                <div class="px-4 py-2 rounded-lg text-sm border border-slate-200 bg-white text-slate-600 peer-checked:bg-indigo-50 peer-checked:border-indigo-300 peer-checked:text-indigo-700 transition-colors font-medium">
                                    Awalan
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="searchMode" value="end" class="peer sr-only">
                                <div class="px-4 py-2 rounded-lg text-sm border border-slate-200 bg-white text-slate-600 peer-checked:bg-indigo-50 peer-checked:border-indigo-300 peer-checked:text-indigo-700 transition-colors font-medium">
                                    Akhiran (Rima)
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="searchMode" value="contain" class="peer sr-only">
                                <div class="px-4 py-2 rounded-lg text-sm border border-slate-200 bg-white text-slate-600 peer-checked:bg-indigo-50 peer-checked:border-indigo-300 peer-checked:text-indigo-700 transition-colors font-medium">
                                    Mengandung
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-4 pt-2 border-t border-slate-200 sm:border-none sm:pt-0">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Karakter</span>
                        <!-- Alpha Only -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" id="alphaFilter" class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-600 font-medium">Hanya Alfabet (A-Z)</span>
                        </label>
                    </div>

                    <div class="space-y-3 pt-2 border-t border-slate-200 sm:border-none sm:pt-0">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Batas Jumlah</span>
                        <!-- Min Length -->
                        <div class="flex items-center justify-between gap-3 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
                            <span class="text-sm text-slate-600 font-medium ml-1">Min Karakter</span>
                            <input type="number" id="minLenFilter" value="1" min="1" max="20" class="w-16 p-1 text-sm border border-slate-200 rounded text-center focus:ring-2 focus:ring-indigo-200 outline-none">
                        </div>
                        <!-- Max Words -->
                        <div class="flex items-center justify-between gap-3 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
                            <span class="text-sm text-slate-600 font-medium ml-1">Max Kata</span>
                            <input type="number" id="maxWordsFilter" value="1" min="1" max="10" class="w-16 p-1 text-sm border border-slate-200 rounded text-center focus:ring-2 focus:ring-indigo-200 outline-none">
                        </div>
                        <!-- Max Limit Results -->
                        <div class="flex items-center justify-between gap-3 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
                            <span class="text-sm text-slate-600 font-medium ml-1">Max Hasil</span>
                            <input type="number" id="limitFilter" value="50" min="1" max="500" class="w-16 p-1 text-sm border border-slate-200 rounded text-center focus:ring-2 focus:ring-indigo-200 outline-none">
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Chips Result Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mt-6 min-h-[200px] flex flex-col">
            <div id="resultList" class="flex flex-wrap gap-2.5 content-start flex-1">
                <div id="statusMessage" class="w-full text-center py-10 text-slate-400 text-sm italic">
                    Ketik sesuatu untuk mulai mencari...
                </div>
            </div>

            <div id="resultFooter" class="mt-8 pt-4 border-t border-slate-100 hidden">
                <div class="flex justify-between items-center">
                    <span id="resultCount" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-1 rounded"></span>
                    <button onclick="resetUI()" class="text-[10px] font-bold text-slate-400 hover:text-red-500 transition-colors uppercase tracking-widest">Bersihkan</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 z-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        <span id="toastMsg" class="text-sm font-medium">Tersalin ke clipboard!</span>
    </div>

    <script>
        // DOM Elements
        const searchInput = document.getElementById('searchInput');
        const filterBtn = document.getElementById('filterBtn');
        const filterPanel = document.getElementById('filterPanel');
        const resultList = document.getElementById('resultList');
        const statusMessage = document.getElementById('statusMessage');
        const resultFooter = document.getElementById('resultFooter');
        const resultCount = document.getElementById('resultCount');
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');

        // Filters
        const sourceFilter = document.getElementById('sourceFilter');
        const alphaFilter = document.getElementById('alphaFilter');
        const minLenFilter = document.getElementById('minLenFilter');
        const maxWordsFilter = document.getElementById('maxWordsFilter');
        const limitFilter = document.getElementById('limitFilter');
        const modeRadios = document.querySelectorAll('input[name="searchMode"]');

        let debounceTimer;
        let toastTimer;
        const currentPath = window.location.pathname;

        // --- Fungsi Utilitas Cookies ---
        function setCookie(name, value, days = 30) {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        // --- Inisialisasi Cookie (Load Preferences) ---
        function loadFiltersFromCookies() {
            if (getCookie('sourceFilter') !== null) sourceFilter.value = getCookie('sourceFilter');
            if (getCookie('alphaFilter') !== null) alphaFilter.checked = getCookie('alphaFilter') === 'true';
            if (getCookie('minLenFilter') !== null) minLenFilter.value = getCookie('minLenFilter');
            if (getCookie('maxWordsFilter') !== null) maxWordsFilter.value = getCookie('maxWordsFilter');
            if (getCookie('limitFilter') !== null) limitFilter.value = getCookie('limitFilter');

            const savedMode = getCookie('searchMode');
            if (savedMode) {
                const radio = document.querySelector(`input[name="searchMode"][value="${savedMode}"]`);
                if(radio) radio.checked = true;
            }
        }

        // --- Simpan Pengaturan ke Cookie ---
        function saveFiltersToCookies() {
            setCookie('sourceFilter', sourceFilter.value);
            setCookie('alphaFilter', alphaFilter.checked);
            setCookie('minLenFilter', minLenFilter.value);
            setCookie('maxWordsFilter', maxWordsFilter.value);
            setCookie('limitFilter', limitFilter.value);
            setCookie('searchMode', getSelectedMode());
        }

        // Panggil saat halaman dimuat
        document.addEventListener('DOMContentLoaded', loadFiltersFromCookies);

        // Toggle UI Filter
        filterBtn.addEventListener('click', () => {
            filterPanel.classList.toggle('open');
            filterBtn.classList.toggle('bg-slate-100');
            filterBtn.classList.toggle('text-indigo-600');
        });

        // Event Listeners untuk perubahan filter
        [sourceFilter, alphaFilter, minLenFilter, maxWordsFilter, limitFilter, ...modeRadios].forEach(el => {
            el.addEventListener('change', () => {
                saveFiltersToCookies(); // Simpan otomatis setiap ada perubahan
                if (searchInput.value.trim().length > 0) {
                    fetchWords(searchInput.value.trim());
                } else if (el === sourceFilter) {
                    // Beri notifikasi kalau ganti sumber
                    statusMessage.innerHTML = 'Sumber kamus diubah. Silakan ketik kata untuk mencari.';
                    statusMessage.classList.remove('hidden');
                }
            });
        });

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            if (query.length === 0) {
                resetUI();
                return;
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchWords(query), 250);
        });

        function getSelectedMode() {
            return document.querySelector('input[name="searchMode"]:checked').value;
        }

        async function fetchWords(query) {
            statusMessage.innerHTML = 'Mencari sekilat cahaya... ⚡';
            statusMessage.classList.remove('hidden');
            resultFooter.classList.add('hidden');

            const source = sourceFilter.value;
            const isAlpha = alphaFilter.checked;
            const minLen = minLenFilter.value;
            const maxW = maxWordsFilter.value;
            const limit = limitFilter.value;
            const mode = getSelectedMode();

            try {
                // Fetch query params dengan parameter sumber kamus
                const url = `${currentPath}?query=${encodeURIComponent(query)}&source=${source}&alpha=${isAlpha}&minLen=${minLen}&maxWords=${maxW}&limit=${limit}&mode=${mode}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Network error');
                const data = await response.json();
                renderChips(data);
            } catch (err) {
                statusMessage.innerHTML = 'Terjadi kesalahan. Pastikan file sumber tersedia di server.';
            }
        }

        function renderChips(words) {
            const oldChips = resultList.querySelectorAll('.chip');
            oldChips.forEach(c => c.remove());

            if (words.length === 0) {
                statusMessage.innerHTML = 'Kata tidak ditemukan pada kamus yang dipilih.';
                statusMessage.classList.remove('hidden');
                resultFooter.classList.add('hidden');
                return;
            }

            statusMessage.classList.add('hidden');
            resultFooter.classList.remove('hidden');
            resultCount.textContent = `${words.length} KATA DITEMUKAN`;

            words.forEach(word => {
                const btn = document.createElement('button');
                btn.className = 'chip px-4 py-2 bg-white rounded-full text-slate-700 text-sm font-medium cursor-pointer';
                btn.textContent = word;

                btn.onclick = () => {
                    copyToClipboard(word);
                    searchInput.value = word;
                };

                resultList.appendChild(btn);
            });
        }

        function copyToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.opacity = 0;
            document.body.appendChild(textArea);
            textArea.select();

            try {
                document.execCommand('copy');
                showToast(`"${text}" disalin!`);
            } catch (err) {
                console.error('Gagal menyalin', err);
            } finally {
                document.body.removeChild(textArea);
            }
        }

        function showToast(message) {
            toastMsg.textContent = message;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        function resetUI() {
            searchInput.value = '';
            const oldChips = resultList.querySelectorAll('.chip');
            oldChips.forEach(c => c.remove());
            statusMessage.innerHTML = 'Ketik sesuatu untuk mulai mencari...';
            statusMessage.classList.remove('hidden');
            resultFooter.classList.add('hidden');
            searchInput.focus();
        }
    </script>
</body>
</html>
