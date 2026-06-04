# EHR Temporal AI Engine & Explainable AI (XAI) Pipeline Terminal

---

## 💻 Project Demonstration Video
[![]()](YOUR_YOUTUBE_OR_GOOGLE_DRIVE_LINK_HERE)
> Click the placeholder or link above to watch the 1-3 minute project walkthrough showcasing the frontend UI, secure backend logic, and dynamic real-time calculations.

---

## 📸 System Screenshots

### 1. Patient Telemetry Input & Real-Time Feature Engineering
![Patient Dashboard](YOUR_SCREENSHOT_1_URL_HERE)
* Patient terminal showing real-time JavaScript BMI and MAP calculus interpolation along with the time-series transmission pipeline.

### 2. Clinical Monitor & Explainable AI (XAI) Visualizer Matrix
![Doctor Matrix Dashboard](YOUR_SCREENSHOT_2_URL_HERE)
* Physician review node featuring stacked Bootstrap progress bars displaying simulated SHAP/LIME neural feature importance weights.

---

## 📄 Project Description

### Scope & Core Purpose
This project is **not a standard hospital administrative automation system** (it contains no scheduling, billing, or inventory features). Instead, it is an academic **Research-Grade Clinical Decision Support & Explainable AI (XAI) Pipeline Engine**. 

Its primary purpose is to act as a production-ready data engineering gateway designed to ingest multi-modal, longitudinal health telemetry from patients, execute real-time mathematical feature engineering, and run a custom-built, vanilla PHP sequential inference engine mimicking a **Recurrent Neural Network (RNN)** loop to evaluate chronic metabolic and cardiovascular disease progression pathways over time.

### Core Architectural Features
* **Role-Based Authentication Engine (CRUD + Sessions):** Implements secure, isolated user workspaces (Patient and Doctor roles) powered by native PHP `session_start()` token mapping and secure `password_hash()` BCRYPT cryptography.
* **Real-Time Bio-Mathematical Interpolation:** Features concurrent, client-side JavaScript calculation layers that compute Body Mass Index (BMI) and Mean Arterial Pressure (MAP) using the clinical standard formula: `MAP = (Systolic + 2 * Diastolic) / 3` instantly as the user types.
* **Simulated Explanatory Recurrent Logic (RNN):** A custom object-oriented backend sequence processor parses historical entries chronologically over time. It scales down past entries using a temporal decay function (`1 / (index + 1)`) to simulate vanishing gradient conditions in recurrent layers.
* **Explainable AI (XAI) Visualization Matrix:** Simulates predictive feature-importance maps (SHAP/LIME methodologies). It normalizes component weights into relative percentages and presents them to medical staff via multi-colored stacked Bootstrap progress bars (Glucose vs. Cardiovascular vs. Lifestyle factors).
* **Physician Verification Gate (Administrative CRUD):** Doctors can dynamically sort data using strict server-side whitelists, adjust data discrepancies, append clinical directives/prescriptions, and change pipeline validation states to update patient alert feeds.

### Tech Stack
* **Backend:** Pure, Vanilla PHP (No external libraries, frameworks, or dependencies).
* **Frontend:** HTML5, Native JavaScript (ES6+), Bootstrap 5 CSS Framework via CDN.
* **Database:** MySQL/MariaDB Relational Time-Series Segregation.

---

## 🚀 Setup & Installation

### Local Setup (XAMPP / WampServer)
1. Clone this repository into your local server root folder (e.g., `C:/xampp/htdocs/ehr-project`).
2. Open your web browser and navigate to `http://localhost/phpmyadmin/`.
3. Create a database named `ehr_db` (or your university-assigned schema name).
4. Go to the **SQL** tab, import the fully consolidated schema structure found in your database setup queries, and execute.
5. Open `config.php` and verify your local port configurations (e.g., `3306` or `3307`).
6. Launch your browser and navigate to `http://localhost/ehr-project/login.php`.

***

# EHR Zamansal Yapay Zeka Motoru ve Açıklanabilir Yapay Zeka (XAI) İş Hattı Terminali

---

## 💻 Proje Tanıtım Videosu
[![]()](YOUR_YOUTUBE_OR_GOOGLE_DRIVE_LINK_HERE)
> Ön yüz arayüzünü, güvenli arka uç mantığını ve dinamik gerçek zamanlı hesaplamaları gösteren 1-3 dakikalık proje tanıtım videosunu izlemek için yukarıdaki bağlantıya tıklayın.

---

## 📸 Uygulama Ekran Görüntüleri

### 1. Hasta Telemetri Girişi ve Gerçek Zamanlı Öznitelik Mühendisliği
![Hasta Paneli](YOUR_SCREENSHOT_1_URL_HERE)
* Zaman serisi veri gönderim hattı ile birlikte gerçek zamanlı JavaScript BMI ve MAP hesaplamasını gösteren hasta terminali arayüzü.

### 2. Klinik Monitör ve Açıklanabilir Yapay Zeka (XAI) Görselleştirici Matrisi
![Doktor Matris Paneli](YOUR_SCREENSHOT_2_URL_HERE)
* Simüle edilmiş SHAP/LIME yapay zeka öznitelik ağırlıklarını yığınlanmış Bootstrap ilerleme çubuklarıyla gösteren doktor değerlendirme arayüzü.

---

## 📄 Proje Açıklaması

### Kapsam ve Temel Amaç
Bu proje, **klasik bir hastane otomasyonu veya randevu sistemi değildir** (fatura, randevu veya oda takibi gibi idari ögeler içermez). Sistem, akademik düzeyde geliştirilmiş bir **Araştırma Sınıfı Klinik Karar Destek ve Açıklanabilir Yapay Zeka (XAI) İş Hattı Motoru**dur.

Temel amacı; hastalardan çok modlu ve boylamsal (longitudinal) sağlık telemetri verilerini toplamak, gerçek zamanlı matematiksel öznitelik mühendisliği (feature engineering) yürütmek ve kronik metabolik/kardiyovasküler hastalık risk eğilimlerini zaman içinde analiz etmek amacıyla bir **Yinelemeli Sinir Ağı (RNN)** döngüsünü taklit eden saf PHP tabanlı ardışık çıkarım motorunu çalıştırmaktır.

### Çekirdek Mimari Özellikleri
* **Rol Tabanlı Yetkilendirme Motoru (CRUD + Sessions):** Yerel PHP `session_start()` belirteç eşleştirmesi ve güvenli `password_hash()` BCRYPT kriptografisi ile desteklenen, birbirinden tamamen izole edilmiş kullanıcı çalışma alanları (Hasta ve Doktor rolleri) sunar.
* **Gerçek Zamanlı Biyo-Matematiksel İnterpolasyon:** Kullanıcı veri girerken eşzamanlı olarak çalışan istemci taraflı JavaScript katmanları sayesinde Vücut Kitle İndeksi (BMI) ve Ortalama Arter Basıncını (MAP) klinik standart formüle göre anlık olarak hesaplar: `MAP = (Sistolik + 2 * Diyastolik) / 3`.
* **Simüle Edilmiş Zamansal Yinelemeli Mantık (RNN):** Nesne yönelimli arka uç dizi işlemcisi, geçmiş verileri kronolojik olarak tarar. Yinelemeli ağlardaki (RNN) sönümlenen gradyan (vanishing gradient) koşullarını simüle etmek için geçmiş verilerin ağırlığını zamansal bir sönümleme fonksiyonu (`1 / (indis + 1)`) ile ölçeklendirir.
* **Açıklanabilir Yapay Zeka (XAI) Görselleştirme Matrisi:** Tahminleme süreçlerindeki öznitelik önem haritalarını simüle eder (SHAP/LIME metodolojileri). Risk bileşenlerini göreceli yüzdelere normalize ederek klinisyenlere çok renkli yığınlanmış Bootstrap ilerleme çubukları halinde sunar (Glikoz, Kardiyovasküler ve Yaşam Tarzı faktörleri etki dağılımları).
* **Hekim Değerlendirme Geçidi (Yönetimsel CRUD):** Doktorlar, katı beyaz liste (whitelist) kurallarına göre verileri dinamik olarak sıralayabilir, veri hatalarını düzeltebilir, klinik direktifler/reçeteler ekleyebilir ve hasta uyarı akışlarını güncellemek için onay durumlarını yönetebilir.

### Teknoloji Yığını
* **Arka Uç (Backend):** Saf, Yalın PHP (Herhangi bir harici kütüphane, framework veya paket kullanılmamıştır).
* **Ön Uç (Frontend):** HTML5, Saf JavaScript (ES6+), CDN üzerinden Bootstrap 5 CSS Kütüphanesi.
* **Veritabanı:** MySQL/MariaDB İlişkisel Zaman Serisi Ayrıştırması.

---

## 🚀 Kurulum Talimatları

### Yerel Kurulum (XAMPP / WampServer)
1. Bu repoyu yerel sunucunuzun kök dizinine kopyalayın (Örn: `C:/xampp/htdocs/ehr-project`).
2. Tarayıcınızdan `http://localhost/phpmyadmin/` adresine gidin.
3. `ehr_db` adında (veya üniversitenizin size atadığı şema adıyla) bir veritabanı oluşturun.
4. **SQL** sekmesine gidin, konsolide edilmiş şema yapısını yapıştırın ve çalıştırın.
5. `config.php` dosyasını açıp yerel port ayarlar