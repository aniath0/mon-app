@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Nouveau Résultat</h1>

    <form method="POST" action="{{ route('resultats.store') }}" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
        @csrf

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de Résultat</label>
            <input type="text" value="LAB-{{ str_pad($nextResultNumber, 4, '0', STR_PAD_LEFT) }}" readonly class="w-full p-3 border rounded-lg bg-gray-100 text-gray-500">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
            <select name="patient_id" required class="w-full p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Sélectionnez un patient</option>
                @foreach($patients as $patient)
                    <option value="{{ $patient->id }}">{{ $patient->name }} ({{ $patient->dossier_number }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Examens</label>
            <select name="exam_ids[]" id="exam_ids" multiple required class="w-full p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-1">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs examens.</p>
        </div>

        <div id="tableau-container" class="hidden space-y-6"></div>

        <div id="global-observation" class="mb-6 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Observation générale</label>
            <textarea name="global_observation" rows="3" class="w-full p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Conclusion globale de l'examen"></textarea>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" id="submit-btn" disabled class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Enregistrer & Générer PDF</button>
        </div>
    </form>
</div>

<style>
.toolbar {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 4px;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 100;
}
.toolbar button {
    padding: 2px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    font-size: 12px;
}
.toolbar button:hover { background: #f3f4f6; }
.toolbar button.active { background: #e0e7ff; border-color: #6366f1; }
.editable-cell {
    min-height: 32px;
    padding: 6px 8px;
    border-radius: 4px;
    outline: none;
    cursor: text;
}
.editable-cell:focus {
    background: #f0f9ff;
    box-shadow: 0 0 0 2px #6366f1;
}
.editable-cell:empty::before {
    content: attr(data-ph);
    color: #9ca3af;
    font-style: italic;
}
.delete-row-btn {
    opacity: 0;
    transition: opacity 0.2s;
}
tr:hover .delete-row-btn { opacity: 1; }
</style>

<div id="toolbar" class="toolbar" onmousedown="event.preventDefault()">
    <button onclick="changeSize(-2)" title="Réduire le texte">A−</button>
    <button onclick="changeSize(2)" title="Agrandir le texte">A+</button>
    <button onclick="formatText('bold')" id="btn-bold"><b>G</b></button>
    <button onclick="formatText('italic')" id="btn-italic"><i>I</i></button>
    <button onclick="formatText('underline')" id="btn-underline"><u>S</u></button>
</div>

<script>
const examSelect = document.getElementById('exam_ids');
const container = document.getElementById('tableau-container');
const submitBtn = document.getElementById('submit-btn');
const globalObs = document.getElementById('global-observation');
const toolbar = document.getElementById('toolbar');

function formatText(cmd) {
    document.execCommand(cmd, false, null);
    updateToolbarState();
}

function updateToolbarState() {
    document.getElementById('btn-bold').classList.toggle('active', document.queryCommandState('bold'));
    document.getElementById('btn-italic').classList.toggle('active', document.queryCommandState('italic'));
    document.getElementById('btn-underline').classList.toggle('active', document.queryCommandState('underline'));
}

document.addEventListener('selectionchange', () => {
    const sel = window.getSelection();
    if (sel && sel.toString().length > 0) {
        const range = sel.getRangeAt(0);
        const rect = range.getBoundingClientRect();
        toolbar.style.display = 'flex';
        toolbar.style.top = (window.scrollY + rect.top - 45) + 'px';
        toolbar.style.left = (window.scrollX + rect.left) + 'px';
        updateToolbarState();
    } else {
        setTimeout(() => {
            if (!toolbar.matches(':hover')) toolbar.style.display = 'none';
        }, 200);
    }
});

toolbar.addEventListener('mouseleave', () => {
    toolbar.style.display = 'none';
});

// Retire une ligne ; la cellule GE/DP (rowspan, hématologie) est conservée et replacée sur la 1re ligne restante.
function removeRow(tr) {
    const table = tr.closest('table');
    const rs = tr.querySelector('td[data-rs]');
    if (rs) rs.remove();
    tr.remove();
    if (rs) {
        const first = table.querySelector('tbody tr');
        if (first) first.appendChild(rs);
    }
    fixGeSpan(table);
}

function fixGeSpan(table) {
    const cell = table.querySelector('td[data-rs]');
    if (!cell) return;
    cell.rowSpan = Math.max(1, table.querySelectorAll('tbody tr').length);
}

function deleteRow(btn) {
    removeRow(btn.closest('tr'));
}

// Supprime seulement une moitié d'une ligne double (hématologie) ; la ligne disparaît si les deux moitiés sont supprimées.
function deleteHalf(btn) {
    const tr = btn.closest('tr');
    const side = btn.dataset.side;
    tr.querySelectorAll(`[data-side="${side}"]`).forEach(td => {
        td.innerHTML = '';
        td.classList.add('bg-gray-100');
    });
    const stillEditable = [...tr.querySelectorAll('.editable-cell')].some(c => !c.closest('[data-rs]'));
    if (!stillEditable) removeRow(tr);
}

// Agrandir / réduire la taille du texte sélectionné.
function changeSize(delta) {
    const sel = window.getSelection();
    if (!sel.rangeCount || sel.isCollapsed) return;
    let node = sel.anchorNode;
    if (node.nodeType === 3) node = node.parentElement;
    const cell = node.closest('.editable-cell');
    if (!cell) return;
    const cur = parseFloat(getComputedStyle(node).fontSize) || 14;
    const next = Math.max(8, Math.min(36, Math.round(cur + delta)));
    document.execCommand('fontSize', false, '7');
    cell.querySelectorAll('font[size="7"]').forEach(f => {
        const span = document.createElement('span');
        span.style.fontSize = next + 'px';
        while (f.firstChild) span.appendChild(f.firstChild);
        span.querySelectorAll('span').forEach(x => { x.style.fontSize = ''; });
        f.replaceWith(span);
    });
    syncHidden(cell);
}

function deleteBtnHTML(side = '') {
    const act = side ? 'deleteHalf(this)' : 'deleteRow(this)';
    return `<button type="button" onclick="${act}" data-side="${side}" class="delete-row-btn ml-1 text-red-400 hover:text-red-600 text-xs font-bold px-1 rounded" title="Supprimer">✕</button>`;
}

function deleteCell() {
    return `<td class="border px-2 py-1 text-center">${deleteBtnHTML()}</td>`;
}

function editableCell(name, placeholder = '', attrs = '') {
    return `<td class="border px-2 py-1 relative" ${attrs}>
        <div contenteditable="true"
             class="editable-cell"
             data-name="${name}"
             data-ph="${placeholder}"
             style="min-width:60px"></div>
        <input type="hidden" name="${name}" value="">
    </td>`;
}

/**
 * Ligne libre : le nom du paramètre est modifiable.
 * layout = cellules APRÈS le nom : 'resultat', 'observation', 'del' (bouton ✕) ou '' (cellule grisée).
 * Sans 'del' dans le layout, le ✕ est ajouté en fin de ligne.
 */
function freeRow(examId, layout, k) {
    const base = `exams[${examId}][free][${k}]`;
    let cells = layout.map(t => t === 'del' ? deleteCell()
        : t ? editableCell(`${base}[${t}]`)
        : '<td class="border px-4 py-2 bg-gray-50"></td>').join('');
    if (!layout.includes('del')) cells += deleteCell();
    return `<tr class="bg-yellow-50">
        ${editableCell(`${base}[param]`, 'Ligne libre : écrivez le nom du paramètre')}
        ${cells}
    </tr>`;
}

function addRowButton(examId, layout) {
    return `<div class="px-4 py-2 border-t bg-white">
        <button type="button" onclick="addFreeRow(this)" data-exam="${examId}" data-layout='${JSON.stringify(layout)}'
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Ajouter une ligne</button>
    </div>`;
}

function addFreeRow(btn) {
    const card = btn.closest('[data-next]');
    const k = parseInt(card.dataset.next, 10);
    card.dataset.next = k + 1;
    const html = freeRow(btn.dataset.exam, JSON.parse(btn.dataset.layout), k);
    const tbody = card.querySelector('tbody');
    const sdw = tbody.querySelector('tr[data-last]');
    if (sdw) sdw.insertAdjacentHTML('beforebegin', html);
    else tbody.insertAdjacentHTML('beforeend', html);
    bindEditable(card);
    fixGeSpan(card.querySelector('table'));
}

function atbRow(examId, i) {
    return `<tr>
        <td class="border px-2 py-1"><input type="text" name="exams[${examId}][antibiotics][${i}][name]" class="w-full p-2 border rounded" placeholder="Nom de l'antibiotique"></td>
        <td class="border px-4 py-2 text-center"><input type="radio" name="exams[${examId}][antibiotics][${i}][result]" value="S"></td>
        <td class="border px-4 py-2 text-center"><input type="radio" name="exams[${examId}][antibiotics][${i}][result]" value="I"></td>
        <td class="border px-4 py-2 text-center"><input type="radio" name="exams[${examId}][antibiotics][${i}][result]" value="R"></td>
        ${deleteCell()}
    </tr>`;
}

function addAtbRow(btn) {
    const card = btn.closest('[data-next]');
    const i = parseInt(card.dataset.next, 10);
    card.dataset.next = i + 1;
    card.querySelector('tbody').insertAdjacentHTML('beforeend', atbRow(btn.dataset.exam, i));
}

const HINT = 'Surlignez le texte pour mettre en forme • ✕ pour supprimer une ligne';

function cardOpen(title, hint = true, next = 1) {
    return `
        <div class="bg-gray-50 rounded-lg shadow-sm overflow-hidden" data-next="${next}">
            <div class="bg-indigo-100 px-4 py-3 font-semibold text-gray-700 border-b flex justify-between items-center">
                <span>${title}</span>
                ${hint ? `<span class="text-xs text-gray-500 font-normal">${HINT}</span>` : ''}
            </div>`;
}

function sdwBlock(examId) {
    const field = (f) => `<span>${f} (<div contenteditable="true" data-name="exams[${examId}][sdw][${f}]" class="editable-cell inline-block w-12 text-center border-b border-gray-400"></div>)</span>
        <input type="hidden" name="exams[${examId}][sdw][${f}]" value="">`;
    const pair = (a, b) => `<div class="flex flex-wrap gap-4 mb-2">${field(a)}${field(b)}</div>`;
    return `
        <tr data-last>
            <td class="border px-4 py-2 font-medium bg-gray-50">SDW</td>
            <td class="border px-4 py-3">
                ${pair('AO','AH')}
                ${pair('BO','BH')}
                ${pair('CO','CH')}
                ${pair('TO','TH')}
                <div class="mt-2 flex items-center gap-2">
                    <span class="font-medium underline">Conclusion :</span>
                    <div contenteditable="true" data-name="exams[${examId}][sdw][conclusion]" class="editable-cell flex-1 border-b border-gray-400"></div>
                    <input type="hidden" name="exams[${examId}][sdw][conclusion]" value="">
                </div>
            </td>
            ${editableCell(`exams[${examId}][sdw][observation]`)}
            ${deleteCell()}
        </tr>`;
}

function getSerologieTable(examId, examName, params) {
    return `${cardOpen(examName)}
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left w-1/4">PARAMÈTRES</th>
                        <th class="border px-4 py-2 text-left w-2/4">RÉSULTATS</th>
                        <th class="border px-4 py-2 text-left w-1/4">OBSERVATIONS</th>
                        <th class="border px-2 py-2 w-8"></th>
                    </tr>
                </thead>
                <tbody>
                    ${params.map((param, i) => `
                    <tr>
                        <td class="border px-4 py-3 font-medium bg-gray-50">${param}</td>
                        ${editableCell(`exams[${examId}][${i}][resultat]`)}
                        ${editableCell(`exams[${examId}][${i}][observation]`)}
                        ${deleteCell()}
                    </tr>
                    `).join('')}
                    ${freeRow(examId, ['resultat', 'observation'], 0)}
                    ${sdwBlock(examId)}
                </tbody>
            </table>
            ${addRowButton(examId, ['resultat', 'observation'])}
        </div>`;
}

function getSerologieVDRLTable(examId, examName) {
    return getSerologieTable(examId, examName, ['ASLO','AgHBs','TPHA','VDRL','GS-RH','CRP','Séro test (VIH)','HCV']);
}

function getSerologieHBTable(examId, examName) {
    return getSerologieTable(examId, examName, ['ASLO','AgHBs','TPHA',"φ de l'HB",'GS-RH','CRP','Séro test (VIH)','HCV']);
}

function getBiochimieTable(examId, examName) {
    const sections = [
        { rows: [
            {nom:'Glycémie à jeun', unite:'g/l', norme:'0.74 - 1.10 g/l'},
            {nom:'Glycémie PP', unite:'g/l', norme:'0.80 - 1.40 g/l'},
            {nom:'Urée', unite:'g/l', norme:'0.10 - 0.50 g/l'},
            {nom:'Créatininémie', unite:'mg/l', norme:'6 - 13 mg/l'},
            {nom:'Uricémie', unite:'mg/l', norme:'30 - 57 mg/l'},
            {nom:'Calcémie', unite:'mg/l', norme:'85 - 105 mg/l'},
            {nom:'Magnésémie', unite:'mg/l', norme:'16 - 25 mg/l'},
            {nom:'Cholestérol total', unite:'g/l', norme:'1.50 - 2.60 g/l'},
            {nom:'HDL Cholestérol', unite:'g/l', norme:'0.35 - 0.65 g/l'},
            {nom:'LDL Cholestérol', unite:'g/l', norme:'< 1.50 g/l'},
            {nom:'Phosphatases alcalines', unite:'U/l', norme:'Adlt: 98-279, Enf: <6'},
            {nom:'Amylasémie', unite:'U/l', norme:'< 90 U/l'},
        ]},
        { title: 'Transaminases', rows: [
            {nom:'TGO', unite:'U/l', norme:'< 40 U/l'},
            {nom:'TGP', unite:'U/l', norme:'< 40 U/l'},
        ]},
        { title: 'Bilirubine', rows: [
            {nom:'Totale', unite:'mg/l', norme:'< 10 mg/l'},
            {nom:'Direct', unite:'mg/l', norme:'< 2.50 mg/l'},
        ]},
        { rows: [
            {nom:'Protéines Totales', unite:'g/l', norme:'62 - 82 g/l'},
            {nom:'Triglycérides', unite:'g/l', norme:'0.40 - 1.40 g/l'},
            {nom:'Gamma GT', unite:'U/l', norme:'H: 11-50, F: 7-32'},
            {nom:'Phosphore', unite:'mg/l', norme:'Adlt: 25-50, Enft: 40-70'},
        ]},
        { title: 'Ionogramme sanguin', rows: [
            {nom:'Na+', unite:'mEq/l', norme:'135 - 145 mEq/l'},
            {nom:'K+', unite:'mEq/l', norme:'3.50 - 5.50 mEq/l'},
            {nom:'Cl-', unite:'mEq/l', norme:'98 - 108 mEq/l'},
        ]},
    ];

    let idx = 0;
    let rowsHTML = '';
    sections.forEach(section => {
        if (section.title) {
            rowsHTML += `<tr><td class="border px-4 py-2 font-bold bg-indigo-50" colspan="5">${section.title}</td></tr>`;
        }
        section.rows.forEach(p => {
            rowsHTML += `
            <tr>
                <td class="border px-4 py-2 font-medium bg-gray-50">${p.nom}</td>
                ${editableCell(`exams[${examId}][${idx}][resultat]`)}
                <td class="border px-4 py-2 text-gray-500 text-center">${p.unite}</td>
                <td class="border px-4 py-2 text-gray-500">${p.norme}</td>
                ${deleteCell()}
            </tr>`;
            idx++;
        });
    });
    rowsHTML += freeRow(examId, ['resultat', '', ''], 0);

    return `${cardOpen(examName)}
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">PARAMÈTRES</th>
                        <th class="border px-4 py-2 text-left">RÉSULTATS</th>
                        <th class="border px-4 py-2 text-center">UNITÉS</th>
                        <th class="border px-4 py-2 text-left">VALEURS NORMALES</th>
                        <th class="border px-2 py-2 w-8"></th>
                    </tr>
                </thead>
                <tbody>${rowsHTML}</tbody>
            </table>
            ${addRowButton(examId, ['resultat', '', ''])}
        </div>`;
}

function getHematoTable(examId, examName) {
    // Même ordre et mêmes index que app/Support/HematoLayout.php (gauche = index pair, droite = index impair).
    const lines = [
        ['G.BLANCS',    '3 à 8 G/l',     'P.Neu',      '1.5 - 6 G/l',     'split'],
        ['G.ROUGES',    '4 à 6 T/l',     'P.Eos',      '0.15 - 0.4 G/l',  'split'],
        ['HB',          '12 à 16 g/dl',  'P.Baso',     '0.05 - 0.15 G/l', 'split'],
        ['HT',          '35 à 45 %',     'Lympho',     '1.5 - 4 G/l',     'split'],
        ['VGM',         '80 à 90 FL',    'Mono',       '0.2 - 0.8 G/l',   'split'],
        ['CCMH',        '30 à 36 g/dl',  'PLAQ.',      '',                'single'],
        ['TCMH',        '25 à 32 pg',    '',           '',                'blank'],
    ];
    const label = (t, n) => n ? `${t} <span class="text-gray-400 text-xs">${n}</span>` : t;
    const th = (side, content) => `<td class="border px-3 py-2 font-medium bg-gray-50 whitespace-nowrap" data-side="${side}">${content}</td>`;
    const del = (side) => `<td class="border px-2 py-1 text-center" data-side="${side}">${deleteBtnHTML(side)}</td>`;

    const rows = lines.map(([l, ln, r, rn, kind], i) => {
        const nameR = `exams[${examId}][${i * 2 + 1}]`;
        let right;
        if (kind === 'split') {
            right = th('R', label(r, rn))
                + editableCell(`${nameR}[resultat]`, '', 'data-side="R"')
                + editableCell(`${nameR}[absolu]`, '', 'data-side="R"')
                + del('R');
        } else if (kind === 'single') {
            right = th('R', label(r, rn))
                + editableCell(`${nameR}[resultat]`, '', 'data-side="R" colspan="2"')
                + del('R');
        } else {
            right = '<td class="border bg-gray-50" colspan="4"></td>';
        }
        return `
                    <tr>
                        ${th('L', label(l, ln))}
                        ${editableCell(`exams[${examId}][${i * 2}][resultat]`, '', 'data-side="L"')}
                        ${del('L')}
                        ${right}
                    </tr>`;
    }).join('');

    // Ligne GE / DP en bas du tableau (index 13), après les lignes libres
    const ge = `
                    <tr data-last>
                        <td class="border px-3 py-2 font-medium bg-gray-50 whitespace-nowrap">GE / DP</td>
                        ${editableCell(`exams[${examId}][13][resultat]`, '', 'colspan="5"')}
                        ${deleteCell()}
                    </tr>`;

    const layout = ['resultat', 'del', '', '', '', ''];
    return `${cardOpen(examName)}
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left" rowspan="2">PARAMÈTRES</th>
                        <th class="border px-4 py-2 text-left" rowspan="2">RÉSULTATS</th>
                        <th class="border px-2 py-2 w-8" rowspan="2"></th>
                        <th class="border px-4 py-2 text-left" rowspan="2">PARAMÈTRES</th>
                        <th class="border px-4 py-2 text-center" colspan="2">RÉSULTATS</th>
                        <th class="border px-2 py-2 w-8" rowspan="2"></th>
                    </tr>
                    <tr>
                        <th class="border px-3 py-2 text-center whitespace-nowrap">POURCENTAGE %</th>
                        <th class="border px-3 py-2 text-center whitespace-nowrap">ABSOLUES G/L</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                    ${freeRow(examId, layout, 0)}
                    ${ge}
                </tbody>
            </table>
            ${addRowButton(examId, layout)}
        </div>`;
}

function getAtbTable(examId, examName) {
    return `${cardOpen(examName + ' (Antibiogramme)', false, 10)}
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">Antibiotique</th>
                        <th class="border px-4 py-2 text-center">Sensible</th>
                        <th class="border px-4 py-2 text-center">Intermédiaire</th>
                        <th class="border px-4 py-2 text-center">Résistant</th>
                        <th class="border px-2 py-2 w-8"></th>
                    </tr>
                </thead>
                <tbody>
                    ${Array.from({length: 10}).map((_, i) => atbRow(examId, i)).join('')}
                </tbody>
            </table>
            <div class="px-4 py-2 border-t bg-white">
                <button type="button" onclick="addAtbRow(this)" data-exam="${examId}"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Ajouter une ligne</button>
            </div>
        </div>`;
}

function getDefaultTable(examId, examName, params) {
    if (!params || params.length === 0) {
        return `${cardOpen(examName)}
                <div class="p-4 text-gray-500 text-sm">
                    Aucun paramètre défini.
                    <a href="/exams" class="text-indigo-600 hover:underline ml-1">Définir des paramètres</a>
                </div>
            </div>`;
    }
    return `${cardOpen(examName)}
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">Paramètre</th>
                        <th class="border px-4 py-2 text-left">Résultat</th>
                        <th class="border px-4 py-2 text-left">Norme</th>
                        <th class="border px-4 py-2 text-left">Observation</th>
                        <th class="border px-2 py-2 w-8"></th>
                    </tr>
                </thead>
                <tbody>
                    ${params.map((row, i) => `
                    <tr>
                        <td class="border px-4 py-2 font-medium bg-gray-50">${row.param || 'Paramètre ' + (i+1)}</td>
                        ${editableCell(`exams[${examId}][${i}][resultat]`)}
                        <td class="border px-4 py-2 text-gray-500">${row.norme || '-'}</td>
                        ${editableCell(`exams[${examId}][${i}][observation]`)}
                        ${deleteCell()}
                    </tr>
                    `).join('')}
                    ${freeRow(examId, ['resultat', '', 'observation'], 0)}
                </tbody>
            </table>
            ${addRowButton(examId, ['resultat', '', 'observation'])}
        </div>`;
}

function syncHidden(cell) {
    const name = cell.dataset.name;
    if (!name) return;
    const hidden = document.querySelector(`input[type="hidden"][name="${name}"]`);
    if (hidden) hidden.value = cell.innerHTML;
}

function bindEditable(root) {
    root.querySelectorAll('.editable-cell').forEach(cell => {
        if (cell.dataset.bound) return;
        cell.dataset.bound = '1';
        cell.addEventListener('input', () => syncHidden(cell));
    });
}

examSelect.addEventListener('change', async () => {
    container.innerHTML = '';
    const selected = Array.from(examSelect.selectedOptions).map(opt => opt.value);

    if (selected.length === 0) {
        container.classList.add('hidden');
        submitBtn.disabled = true;
        globalObs.classList.add('hidden');
        return;
    }

    globalObs.classList.remove('hidden');

    let allHTML = '';
    for (const examId of selected) {
        try {
            const res = await fetch(`/exams/${examId}/params`);
            const data = await res.json();
            // Sans accents et en minuscules : "Sérologie HB" -> "serologie hb"
            const name = data.name.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            let html = '';

            if (name.includes('serologi') && name.includes('hb')) {
                html = getSerologieHBTable(examId, data.name);
            } else if (name.includes('serologi')) {
                html = getSerologieVDRLTable(examId, data.name);
            } else if (name.includes('biochimi')) {
                html = getBiochimieTable(examId, data.name);
            } else if (name.includes('hemato') || name.includes('nfs') || name.includes('numeration')) {
                html = getHematoTable(examId, data.name);
            } else if (name.includes('antibiogramme') || name.includes('atb') || name.includes('spermogramme')) {
                html = getAtbTable(examId, data.name);
            } else {
                html = getDefaultTable(examId, data.name, data.params || []);
            }

            allHTML += html;
        } catch (err) {
            console.error(err);
            allHTML += `<div class="bg-red-100 text-red-700 p-3 rounded-lg">Erreur chargement examen ID: ${examId}</div>`;
        }
    }

    // Une seule écriture dans le DOM, puis on branche les écouteurs.
    container.innerHTML = allHTML;
    container.classList.remove('hidden');
    submitBtn.disabled = false;

    bindEditable(container);
});

document.querySelector('form').addEventListener('submit', function () {
    document.querySelectorAll('.editable-cell').forEach(syncHidden);
});
</script>
@endsection