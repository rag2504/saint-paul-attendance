document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Timetable slot modal handling
    var timetableModalEl = document.getElementById('timetableSlotModal');
    if (timetableModalEl) {
        timetableModalEl.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            if (!btn) return;
            var day = btn.getAttribute('data-day');
            var slot = btn.getAttribute('data-slot');
            var subject = btn.getAttribute('data-subject') || '';
            var classVal = btn.getAttribute('data-class');
            var sectionVal = btn.getAttribute('data-section');

            document.getElementById('tt-class').value = classVal;
            document.getElementById('tt-section').value = sectionVal;
            document.getElementById('tt-day').value = day;
            document.getElementById('tt-slot').value = slot;
            document.getElementById('tt-subject').value = subject;
            document.getElementById('tt-label').textContent = day + ' | ' + slot + ' | Class ' + classVal + ' - ' + sectionVal;
        });
    }

    // Students: search + filters + pagination + edit modal
    var studentsTable = document.getElementById('studentsTable');
    if (studentsTable) {
        var searchEl = document.getElementById('studentSearch');
        var filterClassEl = document.getElementById('filterClass');
        var filterSectionEl = document.getElementById('filterSection');
        var prevBtn = document.getElementById('studentsPrev');
        var nextBtn = document.getElementById('studentsNext');
        var countEl = document.getElementById('studentsCount');

        var rows = Array.prototype.slice.call(studentsTable.querySelectorAll('tbody .student-row'));
        var pageSize = 10;
        var page = 1;

        function getFilteredRows() {
            var q = (searchEl && searchEl.value ? searchEl.value : '').toLowerCase().trim();
            var cls = filterClassEl ? filterClassEl.value : '';
            var sec = filterSectionEl ? filterSectionEl.value : '';

            return rows.filter(function (r) {
                var id = (r.getAttribute('data-id') || '').toLowerCase();
                var name = (r.getAttribute('data-name') || '').toLowerCase();
                var rCls = r.getAttribute('data-class') || '';
                var rSec = r.getAttribute('data-section') || '';

                var matchesQ = !q || id.includes(q) || name.includes(q);
                var matchesClass = !cls || rCls === cls;
                var matchesSection = !sec || rSec === sec;
                return matchesQ && matchesClass && matchesSection;
            });
        }

        function render() {
            var filtered = getFilteredRows();
            var total = filtered.length;
            var totalPages = Math.max(1, Math.ceil(total / pageSize));
            page = Math.min(page, totalPages);
            page = Math.max(page, 1);

            rows.forEach(function (r) { r.style.display = 'none'; });
            var start = (page - 1) * pageSize;
            var slice = filtered.slice(start, start + pageSize);
            slice.forEach(function (r) { r.style.display = ''; });

            if (countEl) {
                countEl.textContent = 'Showing ' + slice.length + ' of ' + total + ' students • Page ' + page + ' / ' + totalPages;
            }
            if (prevBtn) prevBtn.disabled = page <= 1;
            if (nextBtn) nextBtn.disabled = page >= totalPages;
        }

        function resetToFirstPage() {
            page = 1;
            render();
        }

        if (searchEl) searchEl.addEventListener('input', resetToFirstPage);
        if (filterClassEl) filterClassEl.addEventListener('change', resetToFirstPage);
        if (filterSectionEl) filterSectionEl.addEventListener('change', resetToFirstPage);
        if (prevBtn) prevBtn.addEventListener('click', function () { page -= 1; render(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { page += 1; render(); });

        // Edit modal fill
        var editModalEl = document.getElementById('editStudentModal');
        if (editModalEl) {
            editModalEl.addEventListener('show.bs.modal', function (event) {
                var btn = event.relatedTarget;
                if (!btn) return;
                // Prefer button dataset (most reliable)
                var sid = btn.getAttribute('data-id') || '';
                var name = btn.getAttribute('data-name') || '';
                var cls = btn.getAttribute('data-class') || '';
                var sec = btn.getAttribute('data-section') || '';
                var email = btn.getAttribute('data-email') || '';

                var hid = document.getElementById('edit-student-id');
                var sidDisp = document.getElementById('edit-student-id-display');
                var nameEl = document.getElementById('edit-name');
                var clsEl = document.getElementById('edit-class');
                var secEl = document.getElementById('edit-section');
                var emailEl = document.getElementById('edit-email');

                if (hid) hid.value = sid;
                if (sidDisp) sidDisp.value = sid;
                if (nameEl) nameEl.value = name;
                if (clsEl) clsEl.value = cls;
                if (secEl) secEl.value = sec;
                if (emailEl) emailEl.value = email;
            });
        }

        render();
    }
});

