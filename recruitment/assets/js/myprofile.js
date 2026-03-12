      const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

      function formatDob(value) {
        if (!value) return '';
        const parts = value.split('-');
        if (parts.length !== 3) return value;
        const [y, m, d] = parts;
        const monthName = MONTHS[parseInt(m, 10) - 1] || m;
        return `${monthName} ${parseInt(d, 10)}, ${y}`;
      }

      function dobSelectsHTML(stored) {
        let selDay = '', selMonth = '', selYear = '';
        if (stored) {
          const p = stored.split('-');
          if (p.length === 3) { selYear = p[0]; selMonth = String(parseInt(p[1],10)); selDay = String(parseInt(p[2],10)); }
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
          <select class="form-select form-select-sm dob-day" style="width:80px;">${dayOpts}</select>
          <select class="form-select form-select-sm dob-year" style="width:90px;">${yearOpts}</select>
        </div>`;
      }

      function getUserData() {
        const stored = localStorage.getItem('userProfileData');
        if (stored) return JSON.parse(stored);
        return { name:'Alexander', surname:'Pierce', email:'alexander@example.com', address:'123 Main Street', phone:'', dob:'', degree:'', institution:'', specialization:'', experience:'', summary:'' };
      }

      function saveUserData(data) {
        localStorage.setItem('userProfileData', JSON.stringify(data));
      }

      (function initPage() {
        const userData = getUserData();
        document.getElementById('nameCell').textContent = userData.name;
        document.getElementById('surnameCell').textContent = userData.surname;
        document.getElementById('emailCell').textContent = userData.email;
        document.getElementById('addressCell').textContent = userData.address;
        document.getElementById('phoneCell').textContent = userData.phone || '';
        document.getElementById('dobCell').textContent = formatDob(userData.dob);
        // update banner
        const banner = document.getElementById('bannerFullName');
        if (banner) banner.textContent = `${userData.name} ${userData.surname}`;
        const bannerEmail = document.getElementById('bannerEmail');
        if (bannerEmail) bannerEmail.textContent = userData.email;
        const acad = [
          ['degreeCell', userData.degree],
          ['institutionCell', userData.institution],
          ['specializationCell', userData.specialization],
          ['experienceCell', userData.experience !== '' ? userData.experience + (userData.experience === '1' ? ' year' : ' years') : ''],
          ['summaryCell', userData.summary],
        ];
        acad.forEach(([id, val]) => { document.getElementById(id).textContent = val || '\u2014'; });
        const navbarUserName = document.getElementById('navbarUserName');
        if (navbarUserName) navbarUserName.textContent = `${userData.name} ${userData.surname}`;
        const userHeaderP = document.querySelector('.user-header p');
        if (userHeaderP) {
          const subtitle = userHeaderP.querySelector('small');
          const subText = subtitle ? `<small>${subtitle.textContent}</small>` : '';
          userHeaderP.innerHTML = `${userData.name} ${userData.surname} - Web Developer${subText}`;
        }
      })();

      (function () {
        const defaultPic = '../../recruitment/assets/images/user2-160x160.jpg';
        const img = document.getElementById('profilePicSmallBox');
        if (!img) return;
        const url = (window.currentUser && window.currentUser.profilePic) || (window.CareerTrack && window.CareerTrack.profilePic) || defaultPic;
        img.src = url;
      })();

      const editBtn = document.getElementById('editBtn');
      const userFields = document.querySelectorAll('.user-field');
      let isEditMode = false;

      editBtn.addEventListener('click', function() {
        if (!isEditMode) {
          isEditMode = true;
          userFields.forEach(field => {
            const fieldName = field.getAttribute('data-field');
            if (fieldName === 'dob') {
              field.innerHTML = dobSelectsHTML(getUserData().dob);
            } else {
              const originalText = field.textContent;
              const inputType = fieldName === 'phone' ? 'tel' : 'text';
              field.innerHTML = `<input type="${inputType}" class="form-control form-control-sm" value="${originalText}" style="max-width:300px;">`;
            }
          });
          editBtn.textContent = 'Save';
          editBtn.classList.replace('btn-primary', 'btn-success');
        } else {
          const nameInput    = document.querySelector('#nameCell input');
          const surnameInput = document.querySelector('#surnameCell input');
          const addressInput = document.querySelector('#addressCell input');
          const phoneInput   = document.querySelector('#phoneCell input');
          const dobMonth = document.querySelector('#dobCell .dob-month');
          const dobDay   = document.querySelector('#dobCell .dob-day');
          const dobYear  = document.querySelector('#dobCell .dob-year');
          let newName = nameInput ? nameInput.value : 'Alexander';
          let newSurname = surnameInput ? surnameInput.value : 'Pierce';
          let newAddress = addressInput ? addressInput.value : '123 Main Street';
          let newPhone = phoneInput ? phoneInput.value : '';
          let newDob = '';
          if (dobMonth && dobDay && dobYear && dobMonth.value && dobDay.value && dobYear.value) {
            const m = String(parseInt(dobMonth.value,10)).padStart(2,'0');
            const d = String(parseInt(dobDay.value,10)).padStart(2,'0');
            newDob = `${dobYear.value}-${m}-${d}`;
          } else {
            newDob = getUserData().dob || '';
          }
          userFields.forEach(field => {
            const fieldName = field.getAttribute('data-field');
            if (fieldName === 'dob') { field.textContent = formatDob(newDob); }
            else { const input = field.querySelector('input'); if (input) field.textContent = input.value; }
          });
          const userData = getUserData();
          userData.name = newName; userData.surname = newSurname; userData.address = newAddress;
          userData.phone = newPhone; userData.dob = newDob;
          saveUserData(userData);
          const newFullName = `${newName} ${newSurname}`;
          const navbarUserName = document.getElementById('navbarUserName');
          if (navbarUserName) navbarUserName.textContent = newFullName;
          const bannerFullName = document.getElementById('bannerFullName');
          if (bannerFullName) bannerFullName.textContent = newFullName;
          const bannerEmailEl = document.getElementById('bannerEmail');
          if (bannerEmailEl) bannerEmailEl.textContent = userData.email;
          const userHeaderP = document.querySelector('.user-header p');
          if (userHeaderP) {
            const subtitle = userHeaderP.querySelector('small');
            const subText = subtitle ? `<small>${subtitle.textContent}</small>` : '';
            userHeaderP.innerHTML = `${newFullName} - Web Developer${subText}`;
          }
          isEditMode = false;
          editBtn.textContent = 'Edit';
          editBtn.classList.replace('btn-success', 'btn-primary');
        }
      });

      const DEGREE_OPTIONS = ['', 'High School Diploma', "Associate's Degree", "Bachelor's Degree", "Master's Degree", 'Doctoral Degree (PhD)', 'Professional Degree (MD / JD / etc.)', 'Other'];
      const editAcademicBtn = document.getElementById('editAcademicBtn');
      const academicFields  = document.querySelectorAll('.academic-field');
      let isAcademicEditMode = false;

      editAcademicBtn.addEventListener('click', function () {
        const alertBox = document.getElementById('academicValidationAlert');
        alertBox.classList.add('d-none'); alertBox.textContent = '';
        if (!isAcademicEditMode) {
          isAcademicEditMode = true;
          const acadData = getUserData();
          academicFields.forEach(field => {
            const f = field.getAttribute('data-field');
            if (f === 'degree') {
              let opts = DEGREE_OPTIONS.map(o => `<option value="${o}"${acadData.degree === o ? ' selected' : ''}>${o || '\u2014 Select degree \u2014'}</option>`).join('');
              field.innerHTML = `<select class="form-select form-select-sm" style="max-width:300px;" id="degreeSelect">${opts}</select>`;
            } else if (f === 'experience') {
              field.innerHTML = `<input type="number" id="experienceInput" min="0" max="60" class="form-control form-control-sm" style="max-width:120px;" value="${acadData.experience || ''}" placeholder="0">`;
            } else if (f === 'summary') {
              field.innerHTML = `<textarea id="summaryInput" class="form-control form-control-sm" rows="4" style="max-width:500px;" placeholder="Write a short professional bio\u2026">${acadData.summary || ''}</textarea>`;
            } else {
              field.innerHTML = `<input type="text" class="form-control form-control-sm" value="${acadData[f] || ''}" style="max-width:300px;" placeholder="Enter ${f}">`;
            }
          });
          editAcademicBtn.textContent = 'Save';
          editAcademicBtn.classList.replace('btn-primary', 'btn-success');
        } else {
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
          const newDegree  = degreeEl  ? degreeEl.value  : '';
          const newInst    = instInput ? instInput.value : '';
          const newSpec    = specInput ? specInput.value : '';
          const newExp     = expInput  ? expInput.value.trim() : '';
          const newSummary = sumInput  ? sumInput.value  : '';
          document.getElementById('degreeCell').textContent         = newDegree  || '\u2014';
          document.getElementById('institutionCell').textContent    = newInst    || '\u2014';
          document.getElementById('specializationCell').textContent = newSpec    || '\u2014';
          document.getElementById('experienceCell').textContent     = newExp !== '' ? newExp + (newExp === '1' ? ' year' : ' years') : '\u2014';
          document.getElementById('summaryCell').textContent        = newSummary || '\u2014';
          const userData = getUserData();
          userData.degree = newDegree; userData.institution = newInst;
          userData.specialization = newSpec; userData.experience = newExp; userData.summary = newSummary;
          saveUserData(userData);
          isAcademicEditMode = false;
          editAcademicBtn.textContent = 'Edit';
          editAcademicBtn.classList.replace('btn-success', 'btn-primary');
        }
      });
