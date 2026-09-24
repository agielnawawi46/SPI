
    document.addEventListener('DOMContentLoaded', function () {
        let groupCount = 1;

        function updateGroupNumbers() {
            document.querySelectorAll('.group-body').forEach((group, index) => {
                group.dataset.groupIndex = index;
                group.querySelector('.group-number-label').textContent = 'Nomor ' + (index + 1);
                
                // Hide remove button if only 1 group exists
                const removeBtn = group.querySelector('.removeGroup');
                if (document.querySelectorAll('.group-body').length > 1) {
                    removeBtn.classList.remove('hidden');
                } else {
                    removeBtn.classList.add('hidden');
                }

                // Update input names for all rows in this group
                group.querySelectorAll('.item-row').forEach((row, rowIndex) => {
                    updateRowInputNames(row, index, rowIndex);
                });
            });
        }

        function updateRowInputNames(row, gIndex, rIndex) {
            row.querySelectorAll('textarea, input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/items\[\d+\]\[rows\]\[\d+\]/, `items[${groupIndex}][rows][${rowIndex}]`);
                    input.setAttribute('name', newName);
                }
            });
        }

        // Add new Group (Nomor)
        document.getElementById('addRow').addEventListener('click', function () {
            const table = document.getElementById('dynamicTable');
            const tbody = document.createElement('tbody');
            tbody.className = 'group-body';
            
            tbody.innerHTML = `
                <tr class="bg-surface-50 border-b border-surface-200 group-header-row">
                    <td colspan="13" class="px-4 py-2 font-bold text-primary-700">
                        <div class="flex justify-between items-center">
                            <span class="group-number-label flex items-center gap-2">
                                <span class="bg-primary-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs">#</span>
                                Nomor Baru
                            </span>
                            <div class="space-x-3">
                                <button type="button" class="text-primary-600 hover:text-primary-800 text-[11px] uppercase tracking-wider font-bold addSubRow transition-colors">
                                    + Tambah Sub-Baris
                                </button>
                                <button type="button" class="text-red-500 hover:text-red-700 text-[11px] uppercase tracking-wider font-bold removeGroup transition-colors">
                                    - Hapus Nomor Ini
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="item-row hover:bg-surface-50 transition-colors">
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][lha]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly required placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][sebab]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][akibat]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][rekomendasi]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][pic_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><input type="email" name="items[999][rows][0][pic_email]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm" placeholder="email@contoh.com"></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][evidence_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][status_closed]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][status_on_progress]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][status_tdd]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][evaluasi_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><input type="date" name="items[999][rows][0][jatuh_tempo]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm"></td>
                    <td class="p-1.5 text-center align-middle">
                        <button type="button" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-md transition-colors removeRow" title="Hapus Sub-Baris">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
            `;
            table.appendChild(tbody);
            updateGroupNumbers();
        });

        // Event Delegation for Table Actions
        document.getElementById('dynamicTable').addEventListener('click', function(e) {
            // Remove Row
            if (e.target.closest('.removeRow')) {
                const btn = e.target.closest('.removeRow');
                const tbody = btn.closest('.group-body');
                if (tbody.querySelectorAll('.item-row').length > 1) {
                    btn.closest('tr').remove();
                    updateGroupNumbers();
                } else {
                    alert('Grup temuan minimal memiliki satu baris!');
                }
            }
            
            // Remove Group
            if (e.target.closest('.removeGroup')) {
                if (document.querySelectorAll('.group-body').length > 1) {
                    if(confirm('Hapus temuan nomor ini?')) {
                        e.target.closest('.group-body').remove();
                        updateGroupNumbers();
                    }
                }
            }

            // Add Sub Row
            if (e.target.closest('.addSubRow')) {
                const tbody = e.target.closest('.group-body');
                const tr = document.createElement('tr');
                tr.className = 'item-row hover:bg-surface-50 transition-colors';
                tr.innerHTML = `
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][lha]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly required placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][sebab]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][akibat]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][rekomendasi]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][pic_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><input type="email" name="items[999][rows][999][pic_email]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm" placeholder="email@contoh.com"></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][evidence_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][status_closed]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][status_on_progress]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][status_tdd]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][evaluasi_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><input type="date" name="items[999][rows][999][jatuh_tempo]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm"></td>
                    <td class="p-1.5 text-center align-middle">
                        <button type="button" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-md transition-colors removeRow" title="Hapus Sub-Baris">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
                updateGroupNumbers();
            }
        });

        // Initial setup
        updateGroupNumbers();

                        // -----------------------------------------
        // Native Dialog Modal Logic for Textareas
        // -----------------------------------------
        const dialog = document.getElementById('nativeModal');
        const dialogTextarea = document.getElementById('nativeModalTextarea');
        const dialogTitle = document.getElementById('nativeModalTitle');
        const dialogClose = document.getElementById('nativeModalClose');
        const dialogSave = document.getElementById('nativeModalSave');
        let currentTarget = null;
        
        // Always append dialog to body for absolute safety
        document.body.appendChild(dialog);

        document.getElementById('dynamicTable').addEventListener('click', function(e) {
            if (e.target.classList.contains('popup-textarea')) {
                e.preventDefault();
                currentTarget = e.target;
                
                // Find column name for title
                const tr = e.target.closest('tr');
                const tdIndex = Array.from(tr.children).indexOf(e.target.closest('td'));
                const headers = ['LHA', 'Sebab', 'Akibat', 'Rekomendasi', 'PIC TL', 'Email PIC', 'Evidence TL', 'Status Closed', 'Status On Progress', 'Status TDD', 'Evaluasi TL', 'Jatuh Tempo'];
                
                let title = 'Edit Teks';
                if(tdIndex >= 0 && tdIndex < headers.length) {
                    title = 'Edit ' + headers[tdIndex];
                }
                
                dialogTitle.textContent = title;
                dialogTextarea.value = currentTarget.value;
                dialog.showModal();
            }
        });

        dialogClose.addEventListener('click', function() {
            dialog.close();
            currentTarget = null;
        });

        dialogSave.addEventListener('click', function() {
            if (currentTarget) {
                currentTarget.value = dialogTextarea.value;
            }
            dialog.close();
            currentTarget = null;
        });
    });

