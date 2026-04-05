# GymTrack — Spor Salonu Yönetim Sistemi

PHP + MySQL + Bootstrap ile geliştirilmiş spor salonu yönetim uygulaması.

## Güncel Mimari

Proje artık iki ana katmanla ilerliyor:

- `frontend/` → Kullanıcı arayüzü (Dashboard, Üye listesi vb.)
- `backend/` → API ve veri erişim katmanı

Mevcut eski sayfalar `legacy/` altında korunmuştur.

## Durum Özeti (05 Nisan 2026)

- `frontend/` altında çalışan API tabanlı sayfalar:
	- `frontend/index.php` (Dashboard)
	- `frontend/members.php` (Üye listesi)
	- `frontend/trainers.php` (Antrenör listesi)
- `backend/api/` altında çalışan endpointler:
	- `dashboard.php`
	- `members.php`
	- `trainers.php`
- Hatalı oluşan `\{includes,assets` klasörü temizlendi.
- Legacy placeholder sayfalardaki (`$page` tanımsız) hata giderildi.
- Legacy dosyalar kökten `legacy/` klasörüne taşındı.
- Kök `index.php`, yeni arayüz olan `frontend/index.php` adresine yönlendiriyor.

## Klasör Yapısı (Yeni)

- `frontend/includes/layout.php` → Ortak HTML iskeleti ve navbar
- `frontend/assets/js/app.js` → API çağrı yardımcıları
- `frontend/index.php` → API tabanlı dashboard
- `frontend/members.php` → API tabanlı üye listesi
- `frontend/trainers.php` → API tabanlı antrenör listesi
- `backend/config/database.php` → Veritabanı bağlantı sınıfı
- `backend/src/GymRepository.php` → Sorgu/repository katmanı
- `backend/api/dashboard.php` → Dashboard JSON endpoint
- `backend/api/members.php` → Üye listesi JSON endpoint
- `backend/api/trainers.php` → Antrenör listesi JSON endpoint

## Kurulum

1. XAMPP (veya MAMP) kur.
2. Bu klasörü web root altına koy (`htdocs/gym` gibi).
3. Apache + MySQL servislerini başlat.
4. `gym_db` veritabanını oluştur ve tabloları import et.
5. Tarayıcıdan `http://localhost/gym/` aç (otomatik `frontend/index.php`'ye gider).

## Veritabanı Bilgisi

Varsayılan bağlantı bilgileri:

- host: `localhost`
- kullanıcı: `root`
- şifre: `` (boş)
- veritabanı: `gym_db`

Gerekirse `backend/config/database.php` dosyasından güncelleyebilirsin.

## API Endpointleri

- `GET /gym/backend/api/dashboard.php`
- `GET /gym/backend/api/members.php?search=ali&status=active&limit=20`
- `GET /gym/backend/api/trainers.php?search=ayse&status=active&limit=20`

Örnek yanıt formatı:

```json
{
	"ok": true,
	"data": { }
}
```

## Frontend'i Nasıl Geliştireceğiz?

Frontend geliştirme için şu paterni izleyelim:

1. `backend/src/GymRepository.php` içine yeni sorgu metodu ekle.
2. `backend/api/` altında ilgili endpoint dosyasını oluştur.
3. `frontend/` altında sayfa oluştur (`layout.php` + `app.js/apiGet` kullan).
4. Sayfaya filtre formu + tablo + loading/error durumlarını ekle.
5. Navbar linkini `frontend/includes/layout.php` içine ekle.

Bu pattern şu an `members` ve `trainers` modüllerinde uygulanmış durumda.

Önerilen sıradaki sayfalar:

- `frontend/classes.php`
- `frontend/subscriptions.php`
- `frontend/equipment.php`
- `frontend/branches.php`

Her biri için aynı API-first yaklaşım kullanılmalı (server-render değil, endpoint + fetch).
