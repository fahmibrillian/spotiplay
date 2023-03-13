# Project Spotiply

Project Spotiply adalah sebuah aplikasi big data music recommender yang dibuat dengan menggunakan framework Laravel dan teknologi-teknologi seperti Apache Kafka, MongoDB, dan Apache Spark.

Instalasi dan Konfigurasi

-------------------------

  

### Instalasi PHP

  

Pertama-tama, install PHP beserta beberapa paket yang diperlukan dengan menjalankan perintah:

  

lua

  

```bash

sudo  apt  install  php  libapache2-mod-php  php-cli

```

  

### Instalasi dan Konfigurasi Laravel

  

Setelah itu, jalankan perintah `composer update` pada folder `spotiply_client`. Kemudian, copy file `.env.example` menjadi `.env` dan generate key dengan perintah `php artisan key:generate`. Terakhir, jalankan laravel dengan perintah `php artisan serve`.

  

### Instalasi MongoDB

  

Untuk menginstal MongoDB, jalankan perintah:

  

`sudo apt install mongodb`

  

Setelah itu, konfigurasikan MongoDB dengan mengedit file `/etc/mongod.conf` dan menambahkan konfigurasi berikut:

  

yaml

  

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

sudo pecl install rdkafka
```

  

Setelah itu, edit file `/etc/php/7.2/cli/php.ini` dan tambahkan baris berikut:

  

yaml

  

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

  

Terakhir, jalankan aplikasi Spotiplay dengan perintah:

  

bash

  

```bash

spark-submit  --packages  org.apache.spark:spark-sql-kafka-0-10_2.11:2.4.7  spotiplay_latest_played_consumer.py

```