<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dexa.in - Layanan Penyelesaian Tugas Akademik Profesional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .testimonial-card {
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            transform: scale(1.03);
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .animate-bounce {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-20px);
            }

            60% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="dexa.in Logo" class="h-8 w-auto">
                    </div>
                </div>
                <div class="hidden md:flex md:items-center md:space-x-8">
                    <a href="#home"
                        class="nav-link text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Beranda</a>
                    <a href="#services"
                        class="nav-link text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Layanan</a>
                    <a href="#about"
                        class="nav-link text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Tentang
                        Kami</a>
                    <a href="#testimonials"
                        class="nav-link text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Testimoni</a>
                    <a href="#contact"
                        class="nav-link text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Kontak</a>
                    <a href="/admin"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Login
                    </a>
                </div>
                <div class="-mr-2 flex items-center md:hidden">
                    <button type="button" id="mobile-menu-button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <span class="sr-only">Buka menu utama</span>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="pt-2 pb-3 space-y-1 px-6">
                <a href="#home"
                    class="block pl-3 pr-4 py-2 border-l-4 border-blue-500 text-base font-medium text-blue-700 bg-blue-50">Beranda</a>
                <a href="#services"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">Layanan</a>
                <a href="#about"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">Tentang
                    Kami</a>
                <a href="#testimonials"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">Testimoni</a>
                <a href="#contact"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">Kontak</a>
                <div class="mt-4 pl-3 pr-4">
                    <a href="/admin"
                        class="block w-full text-center px-4 py-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg text-white relative overflow-hidden"
        style="background-image: url('{{ asset('images/hero1.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="absolute inset-0 bg-blue-900 bg-opacity-70"></div>
        <div class="relative z-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
                <div class="text-left">
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold leading-relaxed mb-6">
                        Selesaikan Tugasmu <br>Bersama Kami!
                    </h1>
                    <p class="text-lg md:text-xl mb-6 text-blue-100 max-w-3xl">
                        dexa.in menyediakan layanan penyelesaian tugas premium untuk membantu mahasiswa berhasil
                        dalam perjalanan akademik mereka tanpa stres.
                    </p>
                    <div class="flex justify-start">
                        <a href="https://wa.me/15551234567?text=Halo%20Dexa.in,%20saya%20tertarik%20dengan%20layanan%20Anda"
                            target="_blank"
                            class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fab fa-whatsapp text-xl mr-2"></i>
                            Chat WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Scroll indicator -->
        <div class="relative z-10 text-center pb-8 animate-bounce">
            <a href="#services" class="text-white hover:text-blue-200 transition-colors">
                <i class="fas fa-chevron-down text-3xl"></i>
            </a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Layanan Kami</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="service-card bg-white rounded-lg shadow-md p-8 transition duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-book text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Penyelesaian Tugas</h3>
                    <p class="text-gray-600 mb-4">
                        Solusi tugas berkualitas tinggi untuk semua mata pelajaran dan tingkat akademik, diselesaikan
                        tepat waktu dengan penelitian yang mendalam.
                    </p>
                </div>

                <!-- Service 2 -->
                <div class="service-card bg-white rounded-lg shadow-md p-8 transition duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-laptop-code text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Proyek Pemrograman</h3>
                    <p class="text-gray-600 mb-4">
                        Bantuan coding ahli dalam berbagai bahasa pemrograman dengan kode yang bersih, efisien, dan
                        terdokumentasi dengan baik.
                    </p>
                </div>

                <!-- Service 3 -->
                <div class="service-card bg-white rounded-lg shadow-md p-8 transition duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-file-alt text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Skripsi & Tesis</h3>
                    <p class="text-gray-600 mb-4">
                        Layanan penelitian, penulisan, dan editing yang komprehensif untuk skripsi atau tesis Anda
                        dengan ketelitian akademik.
                    </p>
                </div>

                <!-- Service 4 -->
                <div class="service-card bg-white rounded-lg shadow-md p-8 transition duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Analisis Data</h3>
                    <p class="text-gray-600 mb-4">
                        Analisis statistik profesional menggunakan SPSS, R, Python, atau alat lainnya dengan
                        interpretasi hasil yang jelas.
                    </p>
                </div>

                <!-- Service 5 -->
                <div class="service-card bg-white rounded-lg shadow-md p-8 transition duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-pencil-alt text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Penulisan Esai</h3>
                    <p class="text-gray-600 mb-4">
                        Esai yang diteliti dengan baik dan orisinal tentang topik apa pun dengan kutipan dan format yang
                        tepat sesuai persyaratan Anda.
                    </p>
                </div>

                <!-- Service 6 -->
                <div class="service-card bg-white rounded-lg shadow-md p-8 transition duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-question-circle text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Permintaan Khusus</h3>
                    <p class="text-gray-600 mb-4">
                        Memiliki kebutuhan akademik yang unik? Kami dapat menangani permintaan khusus dengan tingkat
                        profesionalisme yang sama.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
            <div class="lg:flex lg:items-center lg:justify-between">
                <div class="lg:w-1/2 mb-12 lg:mb-0">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Tim kami" class="rounded-lg shadow-xl">
                </div>
                <div class="lg:w-1/2 lg:pl-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Tentang dexa.in</h2>
                    <p class="text-gray-600 mb-6">
                        Didirikan pada tahun 2024, dexa.in telah berkembang menjadi nama terpercaya dalam layanan
                        bantuan akademik. Tim kami terdiri dari para profesional yang sangat berkualifikasi dengan gelar
                        lanjutan dari universitas terkemuka di seluruh dunia.
                    </p>
                    <p class="text-gray-600 mb-8">
                        Kami memahami tantangan yang dihadapi mahasiswa dalam menyeimbangkan beban akademik dengan
                        komitmen pribadi. Misi kami adalah memberikan dukungan akademik yang andal dan berkualitas
                        tinggi yang membantu mahasiswa mencapai tujuan pendidikan mereka.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-full mr-4">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Kerahasiaan</h4>
                                <p class="text-gray-600 text-sm">Privasi Anda adalah prioritas utama kami</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-full mr-4">
                                <i class="fas fa-star text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Kualitas</h4>
                                <p class="text-gray-600 text-sm">Pemeriksaan kualitas yang ketat</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-full mr-4">
                                <i class="fas fa-clock text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Ketepatan Waktu</h4>
                                <p class="text-gray-600 text-sm">Selalu diselesaikan tepat waktu</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-full mr-4">
                                <i class="fas fa-headset text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Dukungan</h4>
                                <p class="text-gray-600 text-sm">Layanan pelanggan 24/7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 gradient-bg text-white">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold mb-2">100+</div>
                    <div class="text-blue-100">Klien Puas</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">100%</div>
                    <div class="text-blue-100">Tingkat Keberhasilan</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">20+</div>
                    <div class="text-blue-100">Penulis Ahli</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">24/7</div>
                    <div class="text-blue-100">Dukungan Tersedia</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Apa Kata Klien Kami</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Jangan hanya percaya kata kami. Berikut adalah apa yang dikatakan klien kami tentang layanan kami.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="testimonial-card bg-gray-50 rounded-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">
                        "dexa.in menyelamatkan saya selama minggu ujian akhir! Ahli pemrograman mereka memberikan kode
                        yang sempurna dengan dokumentasi yang sangat baik. Saya tidak bisa meminta layanan yang lebih
                        baik."
                    </p>
                    <div class="flex items-center">
                        <div
                            class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center text-white font-bold mr-4">
                            RS</div>
                        <div>
                            <h4 class="font-bold text-gray-900">Rendi Saputra</h4>
                            <p class="text-sm text-gray-600">Mahasiswa S1 Ilmu Komputer</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="testimonial-card bg-gray-50 rounded-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">
                        "Makalah penelitian yang saya terima ditulis dengan sempurna dengan kutipan yang tepat. Penulis
                        jelas memahami topik saya secara mendalam. Pasti akan menggunakan lagi!"
                    </p>
                    <div class="flex items-center">
                        <div
                            class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center text-white font-bold mr-4">
                            C</div>
                        <div>
                            <h4 class="font-bold text-gray-900">Catrine</h4>
                            <p class="text-sm text-gray-600">Mahasiswa S1 Psikologi</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="testimonial-card bg-gray-50 rounded-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">
                        "Awalnya saya ragu, tetapi dexa.in melampaui semua harapan saya. Skripsi saya diselesaikan lebih
                        cepat dari jadwal dengan plagiarisme nol persen. Sangat direkomendasikan!"
                    </p>
                    <div class="flex items-center">
                        <div
                            class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center text-white font-bold mr-4">
                            BP</div>
                        <div>
                            <h4 class="font-bold text-gray-900">Brian Putra</h4>
                            <p class="text-sm text-gray-600">Mahasiswa S2 MSDM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-50">
        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Siap untuk Meringankan Beban Akademik Anda?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
                Biarkan para ahli kami menangani tugas akademik Anda sementara Anda fokus pada hal yang paling penting.
            </p>
            <a href="#contact"
                class="inline-flex items-center px-8 py-4 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Mulai Hari Ini
                <i class="fas fa-arrow-right ml-3"></i>
            </a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-6 sm:px-8 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Hubungi Kami</h2>
                <p class="text-gray-600 mb-8">
                    Memiliki pertanyaan tentang layanan kami atau siap untuk memesan? Hubungi kami melalui kontak di
                    bawah ini.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <div class="flex items-start">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Email Kami</h4>
                        <p class="text-gray-600">contact@dexa.in</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <i class="fas fa-phone-alt text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Telepon Kami</h4>
                        <p class="text-gray-600">+1 (555) 123-4567</p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Ikuti Kami</h3>
                <div class="flex justify-center space-x-4">
                    <a href="https://instagram.com/dexa.in_" target="_blank"
                        class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                    <a href="https://tiktok.com/@dexa.in_" target="_blank"
                        class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-tiktok text-lg"></i>
                    </a>
                    <a href="https://twitter.com/dexa_in" target="_blank"
                        class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-twitter text-lg"></i>
                    </a>
                    <a href="https://facebook.com/dexa.in" target="_blank"
                        class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="justify-center p-5 text-center">
            <p class="text-gray-400 text-sm">
                &copy; 2025 dexa.in. Semua hak dilindungi.
            </p>
        </div>
    </footer>

    <!-- Back to top button -->
    <button id="back-to-top"
        class="fixed bottom-6 right-6 bg-blue-600 text-white w-12 h-12 rounded-full shadow-lg hidden">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Back to top button
        const backToTopButton = document.getElementById('back-to-top');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('hidden');
            } else {
                backToTopButton.classList.add('hidden');
            }
        });

        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });

                    // Close mobile menu if open
                    const mobileMenu = document.getElementById('mobile-menu');
                    if (!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                }
            });
        });
    </script>
</body>

</html>
