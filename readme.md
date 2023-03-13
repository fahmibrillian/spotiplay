
# Project Spotiply

Project Spotiply adalah sebuah aplikasi big data music recommender yang dibuat dengan menggunakan framework Laravel dan teknologi-teknologi seperti Apache Kafka, MongoDB, dan Apache Spark.

Instalasi dan Konfigurasi

-------------------------
### Instalasi VSCode
Notebook dijalankan menggunakan VSCode sehingga penggunaan VSCode disarankan pada project ini dan menggunakan ekstensi jupyter notebook.
Untuk menginstal ekstensi Jupyter Notebook di VS Code, ikuti langkah-langkah berikut:

1.  Buka VS Code di Ubuntu.
    
2.  Buka tab "Extensions" dengan mengklik ikon ekstensi di sidebar sebelah kiri atau dengan menekan Ctrl + Shift + X.
    
3.  Cari ekstensi "Jupyter" dengan mengetikkan "Jupyter" di kolom pencarian.
    
4.  Pilih ekstensi "Jupyter" dari Microsoft dan klik tombol "Install".

### Instalasi Anaconda
 Selain menggunakan VSCode environtment lainnya yang digunakan pada project ini menggunakan bundle anaconda
Untuk menginstal Anaconda di Ubuntu, ikuti langkah-langkah berikut:


1. Unduh paket instalasi Anaconda dari situs web resmi Anaconda di [https://www.anaconda.com/products/individual](https://www.anaconda.com/products/individual).

2. Buka terminal di Ubuntu.

3. Navigasi ke direktori unduhan dengan perintah cd <path-to-downloads-folder>.

4. Ketik perintah berikut untuk menginstal Anaconda:


```php

bash Anaconda3-<version>-Linux-x86_64.sh

```

Pastikan untuk mengganti `<version>` dengan versi Anaconda yang Anda unduh.

5. Ikuti instruksi instalasi yang ditampilkan di layar.

6. Setelah instalasi selesai, buka terminal baru dan ketik perintah `conda list` untuk memeriksa instalasi Anaconda.

7. Jika berhasil, Anda akan melihat daftar semua paket yang diinstal dengan Anaconda.

### Instalasi PHP

  

Pertama-tama, install PHP beserta beberapa paket yang diperlukan dengan menjalankan perintah:
  

```bash

sudo  apt  install  php  libapache2-mod-php  php-cli

```

  

### Instalasi dan Konfigurasi Laravel

  

Setelah itu, jalankan perintah `composer update` pada folder `spotiply_client`. Kemudian, copy file `.env.example` menjadi `.env` dan generate key dengan perintah `php artisan key:generate`. 

#### Tambahkan .env untuk konfigurasi spotify Oauth

Berikut adalah langkah-langkah untuk menambahkan environment:

    
1.  Buka file `.env` dengan perintah `nano .env`.
    
2.  Tambahkan environment variable baru dengan format `NAMA_VARIABLE=isi_variable`.
    
    Dalam kasus ini, tambahkan:
    
    makefile
    
    ```makefile
    SPOTIFY_CLIENT_ID=<client_id>
    SPOTIFY_CLIENT_SECRET=<client_secret>
    SPOTIFY_REDIRECT_URI=http://127.0.0.1:8000/login/spotify/callback
    ```

    * client_id dan client_secret dapat didapatkan dari [https://developer.spotify.com/dashboard/applications](https://developer.spotify.com/dashboard/applications)
    
3.  Simpan file dengan menekan Ctrl + X dan kemudian tekan Y dan Enter untuk menyimpan perubahan.
    
4.  Pastikan untuk menjalankan perintah `php artisan config:clear` untuk menghapus cache konfigurasi dan memuat ulang variabel lingkungan baru.

Terakhir, jalankan laravel dengan perintah `php artisan serve`.  

### Instalasi MongoDB

  

Untuk menginstal MongoDB, jalankan perintah:

  

`sudo apt install mongodb`

  

Setelah itu, konfigurasikan MongoDB dengan mengedit file `/etc/mongod.conf` dan menambahkan konfigurasi berikut:

  

  

```yaml

# network interfaces

net:

port:  27017

bindIp:  0.0.0.0

```

  

### Instalasi dan Konfigurasi Apache Kafka

  

Untuk menginstal Apache Kafka, jalankan perintah:

  

```bash
sudo apt install librdkafka-dev 

sudo apt install php-dev php-pear 

sudo pecl install rdkafka
```

  

Setelah itu, edit file `/etc/php/7.2/cli/php.ini` dan tambahkan baris berikut:

  

```yaml

extension=rdkafka.so

```

  

Restart VM agar rdkafka dapat bekerja.

  

### Instalasi dan Konfigurasi Apache Spark

  

Untuk menginstal Apache Spark, jalankan perintah:

  

bash

  

```bash

spark-shell --packages org.apache.spark:spark-sql-kafka-0-10_2.11:2.4.7, org.mongodb.spark:mongo-spark-connector_2.11:2.4.1

```

  

Kemudian, copy file `kafka-clients-2.7.0.jar` dari folder Kafka ke folder `spark2.4.7/jars`.

### Jalankan Notebook
Buka dan jalankan Spotiplay.ipynb di vscode untuk penggunaan environtment project
<img src="https://i.ibb.co/Lx4VBKZ/Screenshot-1.jpg" alt="Screenshot-1" border="0">