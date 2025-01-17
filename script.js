let currentSlide = 0;

// Fungsi untuk menggeser slide
function moveSlide(step) {
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;

    // Update currentSlide index
    currentSlide = (currentSlide + step + totalSlides) % totalSlides;

    // Calculate the new translate value to show the correct slide
    const newTransformValue = -currentSlide * 100;

    // Apply the new transform value to the slider
    const slider = document.querySelector('.slider');
    if (slider) {
        slider.style.transform = `translateX(${newTransformValue}%)`;
    }
}

// Fungsi untuk membuka modal login
function openLoginModal() {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const roleSelect = document.getElementById('role');

    // Tampilkan modal login dan sembunyikan modal registrasi
    if (loginModal && registerModal) {
        loginModal.style.display = 'block';
        registerModal.style.display = 'none';
    }

    // Atur visibilitas teks registrasi berdasarkan role yang dipilih
    toggleRegisterText(roleSelect.value);

    // Hanya menambahkan event listener sekali saat membuka modal
    if (!roleSelect.dataset.listenerAdded) {
        roleSelect.addEventListener('change', () => {
            toggleRegisterText(roleSelect.value);
        });
        roleSelect.dataset.listenerAdded = true; // Tandai listener sudah ditambahkan
    }
}

// Fungsi untuk menutup modal login
function closeLoginModal() {
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        loginModal.style.display = 'none';
    }
}

// Fungsi untuk membuka modal registrasi
function openRegisterModal() {
    const registerModal = document.getElementById('registerModal');
    const loginModal = document.getElementById('loginModal');

    // Tampilkan modal registrasi dan sembunyikan modal login
    if (registerModal && loginModal) {
        registerModal.style.display = 'block';
        loginModal.style.display = 'none';
    }
}

// Fungsi untuk menutup modal registrasi
function closeRegisterModal() {
    const registerModal = document.getElementById('registerModal');
    if (registerModal) {
        registerModal.style.display = 'none';
    }
}

// Fungsi untuk mengatur visibilitas teks registrasi berdasarkan role
function toggleRegisterText(role) {
    const registerText = document.getElementById('registerText');
    if (registerText) {
        if (role === 'admin') {
            registerText.style.display = 'none'; // Sembunyikan teks jika admin dipilih
        } else {
            registerText.style.display = 'block'; // Tampilkan teks jika pelanggan dipilih
        }
    }
}
