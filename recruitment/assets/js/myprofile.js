/* ================================================================
   myprofile.js  –  Recruitment Module: My Profile
   User info (name, email, phone, address) is rendered by PHP from DB.
   Academic/extra data is read from window.CareerTrack.profileData
   (populated by PHP from the profile_data JSON column in users table).
   On save, all changes are POST-ed to api/profile.php.
================================================================= */

const API_BASE = '../../api';

const MONTHS = [
  'January','February','March','April','May','June',
  'July','August','September','October','November','December'
];

/* ── Helpers ──────────────────────────────────────────────────── */
function formatDob(value) {
  if (!value) return '';
  const parts = value.split('-');
  if (parts.length !== 3) return value;
  const [y, m, d]   = parts;
  const monthName = MONTHS[parseInt(m, 10) - 1] || m;
  return `${monthName} ${parseInt(d, 10)}, ${y}`;
}

function dobSelectsHTML(stored) {
  let selDay = '', selMonth = '', selYear = '';
  if (stored) {
    const p = stored.split('-');
    if (p.length === 3) { selYear = p[0]; selMonth = String(parseInt(p[1], 10)); selDay = String(parseInt(p[2], 10)); }
  }
  let monthOpts = '<option value="">Month</option>';
  MONTHS.forEach((mn, i) => {
    const v = i + 1;
    monthOpts += `<option value="${v}"${selMonth === String(v) ? ' selected' : ''}>${mn}</option>`;
  });
  let dayOpts = '<option value="">Day</option>';
  for (let d = 1; d <= 31; d++) dayOpts += `<option value="${d}"${selDay === String(d) ? ' selected' : ''}>${d}</option>`;
  const curYear = new Date().getFullYear();
  let yearOpts = '<option value="">Year</option>';
  for (let y = curYear; y >= 1900; y--) yearOpts += `<option value="${y}"${selYear === String(y) ? ' selected' : ''}>${y}</option>`;
  return `<div class="d-flex gap-1" style="max-width:340px;">
    <select class="form-select form-select-sm dob-month" style="width:120px;">${monthOpts}</select>
    <select class="form-select form-select-sm dob-day"   style="width:80px;">${dayOpts}</select>
    <select class="form-select form-select-sm dob-year"  style="width:90px;">${yearOpts}</select>
  </div>`;
}

function getProfileData() {
  return (window.CareerTrack && window.CareerTrack.profileData) || {};
}

function escapeHtml(value) {
  const span = document.createElement('span');
  span.textContent = String(value ?? '');
  return span.innerHTML;
}

/* ── Show a simple inline save-feedback message ───────────────── */
function showSaveFeedback(btn, success) {
  const original = btn.textContent;
  btn.textContent = success ? 'Saved ✓' : 'Error – try again';
  btn.classList.toggle('btn-success', success);
  btn.classList.toggle('btn-danger',  !success);
  btn.classList.remove('btn-outline-primary');
  setTimeout(() => {
    btn.textContent = 'Edit';
    btn.classList.remove('btn-success', 'btn-danger');
    btn.classList.add('btn-outline-primary');
  }, 2500);
}

/* ── Initialise academic cells from PHP-provided data ─────────── */
(function initPage() {
  const pd = getProfileData();
  document.getElementById('dobCell').textContent = formatDob(pd.dob || '');
  const fields = [
    ['degreeCell',         pd.degree         || ''],
    ['institutionCell',    pd.institution    || ''],
    ['specializationCell', pd.specialization || ''],
    ['experienceCell',     pd.experience !== undefined && pd.experience !== ''
                             ? pd.experience + (pd.experience === '1' ? ' year' : ' years')
                             : ''],
    ['summaryCell',        pd.summary        || ''],
  ];
  fields.forEach(([id, val]) => {
    document.getElementById(id).textContent = val || '\u2014';
  });
})();

/* ── Profile picture small box ────────────────────────────────── */
(function () {
  const defaultPic = '../../recruitment/assets/images/user2-160x160.jpg';
  const img = document.getElementById('profilePicSmallBox');
  if (!img) return;
  const url = (window.CareerTrack?.profilePic) || defaultPic;
  img.src = url;
})();

function applyAvatarToUI(src) {
  const profileImg = document.getElementById('profilePicSmallBox');
  if (profileImg) profileImg.src = src;

  const navImages = document.querySelectorAll('.user-menu img.user-image, .user-menu .user-header img.rounded-circle.shadow');
  navImages.forEach((img) => {
    img.src = src;
  });
}

(function initAvatarUpload() {
  const input = document.getElementById('profilePicInput');
  if (!input) return;

  input.addEventListener('change', async function () {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
      alert('Please select a JPG, PNG, GIF or WEBP image.');
      input.value = '';
      return;
    }

    if (file.size > 8 * 1024 * 1024) {
      alert('Image size must be up to 8MB.');
      input.value = '';
      return;
    }

    const previousSrc = document.getElementById('profilePicSmallBox')?.src || '';

    const reader = new FileReader();
    reader.onload = function (e) {
      if (e.target && typeof e.target.result === 'string') {
        applyAvatarToUI(e.target.result);
      }
    };
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append('action', 'update_avatar');
    formData.append('avatar', file);

    try {
      const resp = await fetch(`${API_BASE}/profile.php`, {
        method: 'POST',
        body: formData,
      });
      const data = await resp.json();

      if (!resp.ok || !data.success) {
        if (previousSrc) applyAvatarToUI(previousSrc);
        alert(data.error || 'Avatar upload failed. Please try again.');
        input.value = '';
        return;
      }

      if (data.avatar_src) {
        applyAvatarToUI(data.avatar_src);
        if (window.CareerTrack) window.CareerTrack.profilePic = data.avatar_src;
      }
    } catch (e) {
      if (previousSrc) applyAvatarToUI(previousSrc);
      alert('Avatar upload failed. Please try again.');
    } finally {
      input.value = '';
    }
  });
})();

/* ── User Info section (name, surname, address, phone, dob) ────── */
const editBtn    = document.getElementById('editBtn');
const userFields = document.querySelectorAll('.user-field');
let isEditMode   = false;

editBtn.addEventListener('click', async function () {
  if (!isEditMode) {
    // Switch to edit mode
    isEditMode = true;
    userFields.forEach(field => {
      const fieldName  = field.getAttribute('data-field');
      if (fieldName === 'dob') {
        field.innerHTML = dobSelectsHTML(getProfileData().dob || '');
      } else {
        const currentText = field.textContent === '\u2014' ? '' : field.textContent;
        const inputType   = fieldName === 'phone' ? 'tel' : 'text';
        field.innerHTML = `<input type="${inputType}" class="form-control form-control-sm" value="${escapeHtml(currentText)}" style="max-width:300px;">`;
      }
    });
    editBtn.textContent = 'Save';
    editBtn.classList.replace('btn-outline-primary', 'btn-success');
  } else {
    // Collect values
    const nameInput    = document.querySelector('#nameCell input');
    const surnameInput = document.querySelector('#surnameCell input');
    const addressInput = document.querySelector('#addressCell input');
    const phoneInput   = document.querySelector('#phoneCell input');
    const dobMonth = document.querySelector('#dobCell .dob-month');
    const dobDay   = document.querySelector('#dobCell .dob-day');
    const dobYear  = document.querySelector('#dobCell .dob-year');

    let newDob = '';
    if (dobMonth?.value && dobDay?.value && dobYear?.value) {
      const m = String(parseInt(dobMonth.value, 10)).padStart(2, '0');
      const d = String(parseInt(dobDay.value,   10)).padStart(2, '0');
      newDob  = `${dobYear.value}-${m}-${d}`;
    } else {
      newDob = getProfileData().dob || '';
    }

    const newFirstName = nameInput?.value.trim()    || '';
    const newLastName  = surnameInput?.value.trim() || '';
    const newAddress   = addressInput?.value.trim() || '';
    const newPhone     = phoneInput?.value.trim()   || '';

    // Restore DOM to text
    userFields.forEach(field => {
      const fn = field.getAttribute('data-field');
      if (fn === 'dob') { field.textContent = formatDob(newDob) || '\u2014'; }
      else { const inp = field.querySelector('input'); if (inp) field.textContent = inp.value || '\u2014'; }
    });
    isEditMode = false;
    editBtn.textContent = 'Save';

    // Persist to server
    try {
      const existingPd = getProfileData();
      const resp = await fetch(`${API_BASE}/profile.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          first_name:   newFirstName,
          last_name:    newLastName,
          phone:        newPhone,
          address:      newAddress,
          profile_data: { ...existingPd, dob: newDob },
        }),
      });
      const data = await resp.json();
      if (data.success) {
        // Update cached profileData so subsequent edits use the new DOB
        if (window.CareerTrack) {
          window.CareerTrack.profileData = { ...existingPd, dob: newDob };
          window.CareerTrack.firstName = newFirstName;
          window.CareerTrack.fullName = `${newFirstName} ${newLastName}`.trim();
        }
        try {
          localStorage.setItem('userProfileData', JSON.stringify({
            name: newFirstName,
            surname: newLastName,
            email: document.getElementById('emailCell')?.textContent.trim() || '',
            phone: newPhone,
            address: newAddress,
          }));
        } catch (storageError) {
          // Ignore storage sync failures and keep the server update as source of truth.
        }
        // Update banner name
        const bannerFullName = document.getElementById('bannerFullName');
        if (bannerFullName) bannerFullName.textContent = `${newFirstName} ${newLastName}`.trim();
        showSaveFeedback(editBtn, true);
      } else {
        showSaveFeedback(editBtn, false);
      }
    } catch (e) {
      showSaveFeedback(editBtn, false);
    }
  }
});

/* ── Academic / Professional section ─────────────────────────── */
const DEGREE_OPTIONS = [
  '', 'High School Diploma', "Associate's Degree", "Bachelor's Degree",
  "Master's Degree", 'Doctoral Degree (PhD)', 'Professional Degree (MD / JD / etc.)', 'Other'
];
const editAcademicBtn = document.getElementById('editAcademicBtn');
const academicFields  = document.querySelectorAll('.academic-field');
let isAcademicEditMode = false;

editAcademicBtn.addEventListener('click', async function () {
  const alertBox = document.getElementById('academicValidationAlert');
  alertBox.classList.add('d-none');
  alertBox.textContent = '';

  if (!isAcademicEditMode) {
    // Switch to edit mode
    isAcademicEditMode = true;
    const pd = getProfileData();
    academicFields.forEach(field => {
      const f = field.getAttribute('data-field');
      if (f === 'degree') {
        let opts = DEGREE_OPTIONS.map(o =>
          `<option value="${o}"${pd.degree === o ? ' selected' : ''}>${o || '\u2014 Select degree \u2014'}</option>`
        ).join('');
        field.innerHTML = `<select class="form-select form-select-sm" style="max-width:300px;" id="degreeSelect">${opts}</select>`;
      } else if (f === 'experience') {
        field.innerHTML = `<input type="number" id="experienceInput" min="0" max="60" class="form-control form-control-sm" style="max-width:120px;" value="${escapeHtml(pd.experience || '')}" placeholder="0">`;
      } else if (f === 'summary') {
        field.innerHTML = `<textarea id="summaryInput" class="form-control form-control-sm" rows="4" style="max-width:500px;" placeholder="Write a short professional bio…">${escapeHtml(pd.summary || '')}</textarea>`;
      } else {
        field.innerHTML = `<input type="text" class="form-control form-control-sm" value="${escapeHtml(pd[f] || '')}" style="max-width:300px;" placeholder="Enter ${f}">`;
      }
    });
    editAcademicBtn.textContent = 'Save';
    editAcademicBtn.classList.replace('btn-outline-primary', 'btn-success');
  } else {
    // Validate
    const expInput = document.getElementById('experienceInput');
    if (expInput) {
      const expVal = expInput.value.trim();
      if (expVal !== '' && (isNaN(expVal) || Number(expVal) < 0 || !Number.isInteger(Number(expVal)))) {
        alertBox.textContent = 'Years of Professional Experience must be a non-negative whole number.';
        alertBox.classList.remove('d-none');
        expInput.classList.add('is-invalid');
        return;
      }
      expInput.classList.remove('is-invalid');
    }

    const degreeEl  = document.getElementById('degreeSelect');
    const instInput = document.querySelector('#institutionCell input');
    const specInput = document.querySelector('#specializationCell input');
    const sumInput  = document.getElementById('summaryInput');

    const newDegree  = degreeEl?.value  || '';
    const newInst    = instInput?.value || '';
    const newSpec    = specInput?.value || '';
    const newExp     = expInput?.value.trim()  || '';
    const newSummary = sumInput?.value  || '';

    // Restore DOM
    document.getElementById('degreeCell').textContent         = newDegree  || '\u2014';
    document.getElementById('institutionCell').textContent    = newInst    || '\u2014';
    document.getElementById('specializationCell').textContent = newSpec    || '\u2014';
    document.getElementById('experienceCell').textContent     = newExp !== ''
      ? newExp + (newExp === '1' ? ' year' : ' years') : '\u2014';
    document.getElementById('summaryCell').textContent        = newSummary || '\u2014';
    isAcademicEditMode = false;
    editAcademicBtn.textContent = 'Save';

    // Persist to server
    try {
      const existingPd = getProfileData();
      const newPd = {
        ...existingPd,
        degree:         newDegree,
        institution:    newInst,
        specialization: newSpec,
        experience:     newExp,
        summary:        newSummary,
      };

      // Also need current user info for the update call
      const firstName = document.getElementById('nameCell')?.textContent.replace('\u2014','').trim()
        || (window.CareerTrack?.fullName?.split(' ')[0] || '');
      const lastName  = document.getElementById('surnameCell')?.textContent.replace('\u2014','').trim()
        || (window.CareerTrack?.fullName?.split(' ').slice(1).join(' ') || '');
      const phone   = document.getElementById('phoneCell')?.textContent.replace('\u2014','').trim() || '';
      const address = document.getElementById('addressCell')?.textContent.replace('\u2014','').trim() || '';

      const resp = await fetch(`${API_BASE}/profile.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          first_name:   firstName,
          last_name:    lastName,
          phone,
          address,
          profile_data: newPd,
        }),
      });
      const data = await resp.json();
      if (data.success) {
        if (window.CareerTrack) window.CareerTrack.profileData = newPd;
        showSaveFeedback(editAcademicBtn, true);
      } else {
        showSaveFeedback(editAcademicBtn, false);
      }
    } catch (e) {
      showSaveFeedback(editAcademicBtn, false);
    }
  }
});
