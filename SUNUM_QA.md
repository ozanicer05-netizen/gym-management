# GymTrack Sunum — Olası Sorular ve Cevaplar

## Veritabanı / Tasarım

**S: Neden 20 tablo? Bazılarını birleştiremez miydiniz? (örn. users ve members)**
C: Tek tabloda toplasaydık her satırda boş kalan alanlar olurdu — bir admin'in birth_date'i veya emergency_contact'ı olmaz, bir member'ın specialization'ı olmaz. Ayrıca bir kişi hem trainer hem member olabiliyor; tek tabloda bunu temiz modelleyemezdik. Şu anki yapıda `users` ortak hesap bilgilerini, `members` ve `trainers` ise role-spesifik alanları tutuyor. Bu 3NF normalizasyona uygun.

**S: Normalizasyon hangi formda? 3NF mi?**
C: Evet, tüm tablolar 3NF'de. Tekrarlanan grup yok (1NF), tüm non-key alanlar primary key'e tam bağımlı (2NF), transitif bağımlılık yok (3NF). Örnek: `members` tablosunda kullanıcı adı tutmuyoruz, sadece `user_id` foreign key'i — ad bilgisi `users`'tan join'le geliyor.

**S: Neden user_roles junction tablosu? Bir kullanıcı gerçekten birden fazla rol alabiliyor mu?**
C: Evet — örneğin bir kişi hem trainer hem manager olabilir. Tek role kolonu koysaydık bu modellenemezdi. Junction tablo many-to-many ilişki için standart yaklaşım.

**S: Cascade delete yerine soft delete düşündünüz mü?**
C: Status alanlarımız zaten soft delete davranışı sağlıyor — bir member'ı "inactive" yapabilirsin, kayıt durur. Hard delete sadece staff'in gerçekten silmek istediği durumlar için, ve cascade ile tutarlılığı garanti ediyor. Production'da soft delete'i tercih ederdik ama proje kapsamında her ikisini de gösterdik.

**S: Hangi kolonlara index koydunuz?**
C: Tüm primary key'ler ve foreign key'ler otomatik indexli. Ek olarak `users.email` UNIQUE index'li (hem performans hem duplicate önleme için), `subscriptions.end_date` "expiring soon" sorgusu için, `payments.payment_date` aylık revenue için.

**S: ER diyagramınız var mı?**
C: Evet, `database/` klasöründe schema dosyamız var, oradan görsel diyagram da çıkarılabilir. Sunumda göstermedik çünkü 20 tablo tek slayta sığmıyordu.

---

## Güvenlik

**S: `real_escape_string` kullanıyorsunuz — neden prepared statement değil?**
C: Bu haklı bir eleştiri. Şu an karma durum: yeni user oluştururken (`createUser` metodunda) prepared statement + bind_param kullanıyoruz, oraya bcrypt hash'i de güvenli giriyor. Listeleme/filtreleme sorgularında `real_escape_string` ile escape ediyoruz. Production'a alırken hepsini prepared statement'a çevirmek ilk yapılacak iş — şu anki yaklaşım da SQL injection'a karşı koruma sağlıyor ama prepared statement endüstri standardı.

**S: CSRF koruması var mı?**
C: Şu an yok. Aynı-origin politikası ve session cookie'leri temel koruma sağlıyor; production için CSRF token middleware ekleyeceğiz.

**S: Session hijacking'e karşı ne yapıyorsunuz?**
C: PHP session cookie'leri kullanıyoruz. Production'da HTTPS zorunlu olur, cookie'lere `Secure` ve `HttpOnly` flag'leri set edilir. `session_regenerate_id` login sonrası eklenebilir — şu an eklenmedi, bu da iyileştirme listesinde.

**S: Brute force / rate limiting var mı?**
C: Şu an uygulama seviyesinde yok — web sunucusu (nginx/Apache) seviyesinde rate limit kurulması planlandı. Bcrypt hash'i tek başına brute force'u yavaşlatıyor ama hesap kilitleme mantığı production önceliği.

**S: Rol bazlı yetkilendirme nasıl? Admin olmayan biri member silebilir mi?**
C: Şu an her giriş yapmış staff aynı yetkilere sahip — `AuthGuard::requireApiAuth` sadece login kontrolü yapıyor. Role-based authorization (admin / manager / staff farkı) bir sonraki sürümde. `roles` tablomuz hazır, sadece endpoint seviyesinde kontrol eklenmedi.

**S: Bcrypt cost factor kaç?**
C: PHP default'u kullanıyoruz (cost=10), endüstri standardı. Donanım gücüne göre 12'ye çıkarılabilir.

---

## Mimari / Kod

**S: Neden framework (Laravel/Symfony) kullanmadınız?**
C: Bu bir veritabanı dersi projesi — odak SQL tasarımı, ilişkiler, sorgular. Framework kullansaydık ORM tüm SQL'i gizlerdi ve öğrenmek istediğimiz şeyi gösteremezdik. Saf PHP + Repository pattern ile SQL'i açık bıraktık, böylece JOIN, GROUP BY, cascade davranışları doğrudan görülebiliyor.

**S: Repository pattern niye seçildi?**
C: Tüm SQL tek bir sınıfta (`GymRepository`) toplandı — endpoint'ler ince bir HTTP katmanı, sorgu mantığı izole. Bu test edilebilirliği ve değişiklik maliyetini düşürüyor. Aynı zamanda API'lerin tutarlı `ApiResponse` formatında JSON dönmesini sağlıyor.

**S: Test var mı?**
C: Otomatik test suite şu an yok. Seed data ile manuel test ediyoruz — 100+ kullanıcı, 50+ subscription içeren gerçekçi bir veri seti var. Production'a alırken PHPUnit ile en az repository katmanı için unit test gerekir.

**S: Transaction kullanıyor musunuz?**
C: Tek-statement insert'lerde gerekli değil. `createMember` gibi user+member iki insert birden yapan akışlarda eklemek gerekirdi — şu an eklemedik, hata durumunda yarım kayıt kalabilir. Production önceliği.

---

## Özellik / İş Mantığı

**S: Üye kendi kendini kaydedebiliyor mu? Sign-up var mı?**
C: Var — admin login sonrası "Users" sayfası üzerinden rolsüz user oluşturulabiliyor. Bu user sonradan Members veya Trainers sayfasından "Existing User'ı seç" akışıyla profile bağlanıyor. Public self-registration yok çünkü bu bir staff yönetim paneli — üye gym'e gelir, masada staff onu sisteme girer. Gerçek bir gym akışı budur.

**S: Çakışan class rezervasyonlarını nasıl önlüyorsunuz?**
C: `class_reservations` tablosunda member_id + schedule_id üzerinde UNIQUE constraint var, aynı member aynı oturuma iki kez rezervasyon yapamaz. Class kapasite kontrolü (capacity dolu mu) sorgu seviyesinde — rezervasyon eklerken count alıp capacity ile karşılaştırıyoruz.

**S: Equipment maintenance kaydı UI'dan nasıl giriliyor?**
C: `maintenance_records` tablosu var ama UI şu an sadece equipment status'unu (active/maintenance/out of order) değiştiriyor. Detaylı servis kaydı (tarih, açıklama, maliyet) bir sonraki sürümde dedicated bir maintenance log sayfası olacak. Şimdilik veritabanı seviyesinde manuel girilebiliyor.

**S: Bildirimler gerçek email/SMS mi?**
C: Hayır, `notifications` tablosuna kayıt ediliyor; dashboard üzerinden gösterilebilir. Gerçek email/SMS gönderimi için PHPMailer veya Twilio entegrasyonu eklenecek — şu an mimari hazır, sadece dış servis bağlantısı yok.

**S: Ödeme entegrasyonu (Stripe/iyzico) var mı?**
C: Yok. `payments` tablosu manuel girişe göre tasarlandı — staff kart/nakit/havale aldığında kaydı sisteme giriyor. Online ödeme akışı için Stripe webhook entegrasyonu eklenebilir, `payment_method` enum'u zaten "online"ı içeriyor.

**S: Çoklu dil/para birimi desteği?**
C: Şu an tek dil (İngilizce arayüz), tek para birimi (TL veya USD — kullanıcıya bağlı, sembol UI seviyesinde). i18n için Symfony Translator gibi bir çözüm eklenebilir.

**S: Attendance check-in nasıl yapılıyor?**
C: `attendance_logs` tablosu check-in/check-out kayıtlarını tutuyor. Şu an manuel API çağrısıyla. Production'da turnstile/RFID kart okuyucu entegrasyonu yapılabilir — endpoint hazır, donanım entegrasyonu eklenecek.

---

## Performans / Ölçek

**S: Dashboard her açılışta SUM/COUNT yapıyor — 100k kullanıcıda yavaşlamaz mı? Cache var mı?**
C: Şu an cache yok, her dashboard yüklemesinde sorgular çalışıyor. 100k kayıt'a kadar foreign key index'leri ile performans kabul edilebilir (modern MySQL bunu milisaniye seviyesinde halleder). Daha büyük ölçek için Redis cache (5-10 dakikalık TTL) veya materialized view yaklaşımı uygularız. Dashboard analitiği "anlık doğru" olmak zorunda değil, küçük gecikme tolere edilebilir.

**S: Pagination 50 kayıt — neden? Sıralama nasıl?**
C: 50, kullanıcı deneyimi ile sorgu hızı dengesi — sayfa hızlı yüklenir, scroll yormaz. Backend parametreyle değiştirilebiliyor (max 200). Sıralama default olarak ID descending (en yeni üstte); production'da kolon başlıklarına tıklayarak sort eklenir.

**S: Bir branch'i silersem ne olur?**
C: `branches` tablosundaki foreign key constraint'ler nedeniyle, branch'e bağlı member/trainer/equipment varsa silinmez — DB hata fırlatır. Bu kasıtlı: branches sistemin temeli, yanlışlıkla silinmesi felaket olur. Önce o branch'in tüm bağlantıları taşınmalı veya silinmeli.

---

## Genel / Yumuşak Sorular

**S: En zor kısım neydi?**
C: 20 tabloyu birbirine doğru foreign key'lerle bağlamak. Özellikle cascade davranışını doğru kurgulamak — bir user silinince member silinmeli, ama member silinince user silinmemeli (kullanıcı başka rolde olabilir). Bunu test etmek için seed data ile manuel senaryolar yürüttük.

**S: Tekrar yapsanız ne değiştirirdiniz?**
C: (1) Tüm SQL'i prepared statement'a çevirirdik. (2) Test suite eklerdik. (3) Migration sistemi (Phinx vb.) kullanırdık — schema değişikliklerini düz SQL dosyası olarak takip etmek zorlaştı.

**S: Kaç kişi çalıştınız, nasıl böldünüz?**
C: 5 kişi — her birimiz bir modülün UI'ından sorumlu olduk, veritabanı şemasını birlikte tasarladık. Git ile collaborate ettik, conflict çözmek için düzenli pair sessions yaptık.

**S: Canlıya alacak mısınız?**
C: Bu bir akademik proje — istenirse küçük bir gym'de pilot olarak deploy edilebilir. Önceden listelediğimiz güvenlik iyileştirmeleri (CSRF, rate limit, role-based auth, prepared statements) tamamlanmalı.
