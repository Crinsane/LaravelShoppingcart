## LaravelShoppingcart
[![Build Status](https://travis-ci.org/bumbummen99/LaravelShoppingcart.png?branch=master)](https://travis-ci.org/bumbummen99/LaravelShoppingcart)
[![codecov](https://codecov.io/gh/bumbummen99/LaravelShoppingcart/branch/master/graph/badge.svg)](https://codecov.io/gh/bumbummen99/LaravelShoppingcart)
[![StyleCI](https://styleci.io/repos/152610878/shield?branch=master)](https://styleci.io/repos/152610878)
[![Total Downloads](https://poser.pugx.org/bumbummen99/shoppingcart/downloads.png)](https://packagist.org/packages/bumbummen99/shoppingcart)
[![Latest Stable Version](https://poser.pugx.org/bumbummen99/shoppingcart/v/stable)](https://packagist.org/packages/bumbummen99/shoppingcart)
[![Latest Unstable Version](https://poser.pugx.org/bumbummen99/shoppingcart/v/unstable)](https://packagist.org/packages/bumbummen99/shoppingcart)
[![License](https://poser.pugx.org/bumbummen99/shoppingcart/license)](https://packagist.org/packages/bumbummen99/shoppingcart)

Ini adalah percabangan dari [Crinsane's LaravelShoppingcart](https://github.com/Crinsane/LaravelShoppingcart) dikembangkan dengan fitur-fitur minor yang kompatibel dengan Laravel 6

## Instalasi

Install paket(https://packagist.org/packages/bumbummen99/shoppingcart) menggunakan [Composer](http://getcomposer.org/). 

Jalankan Composer dengan menggunakan perintah berikut:

    composer require bumbummen99/shoppingcart

Sekarang Anda siap untuk mulai menggunakan shoppingcart di aplikasi Anda.

**Pada versi 2 dari paket ini memungkinkan untuk menggunakan injeksi dependensi untuk memasukkan instance Class Cart ke controller Anda atau Class lain**

## Gambaran
Lihat salah satu topik berikut untuk mempelajari lebih lanjut tentang LaravelShoppingcart

* [Usage](#usage)
* [Collections](#collections)
* [Instances](#instances)
* [Models](#models)
* [Database](#database)
* [Exceptions](#exceptions)
* [Events](#events)
* [Example](#example)

## Penggunaan

Shoppingcart memberi Anda metode berikut untuk digunakan:

### Cart::add()

Menambahkan item ke troli sangat sederhana, Anda cukup menggunakan metode `add ()`, yang menerima berbagai parameter.

Dalam bentuknya yang paling mendasar, Anda dapat menentukan id, nama, jumlah, harga, dan berat produk yang ingin Anda tambahkan ke troli.

```php
Cart::add('293ad', 'Product 1', 1, 9.99, 550);
```

Sebagai opsional parameter kelima, Anda dapat memberikan opsi, sehingga Anda dapat menambahkan beberapa item dengan id yang sama, tetapi dengan (instance) ukuran yang berbeda.

```php
Cart::add('293ad', 'Product 1', 1, 9.99, 550, ['size' => 'large']);
```

**Metode `add ()` akan mengembalikan instance CartItem dari item yang baru saja Anda tambahkan ke troli.**

Mungkin Anda lebih suka menambahkan item menggunakan array? Selama array berisi kunci yang diperlukan, Anda bisa meneruskannya ke metode. Tombol opsi adalah opsional.

```php
Cart::add(['id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 9.99, 'weight' => 550, 'options' => ['size' => 'large']]);
```

Baru dalam versi 2 paket ini adalah kemungkinan untuk bekerja dengan antarmuka [Buyable] (#buyable). Cara kerjanya adalah bahwa Anda memiliki model yang mengimplementasikan antarmuka [Buyable] (#buyable), yang akan membuat Anda menerapkan beberapa metode sehingga paket tahu bagaimana cara mendapatkan id, nama, dan harga dari model Anda.
Dengan cara ini Anda bisa meneruskan metode `add ()` model dan kuantitas dan secara otomatis akan menambahkannya ke troli.

**Sebagai bonus tambahan, itu akan secara otomatis mengaitkan model dengan CartItem**

```php
Cart::add($product, 1, ['size' => 'large']);
```
Sebagai parameter ketiga opsional, Anda dapat menambahkan opsi.
```php
Cart::add($product, 1, ['size' => 'large']);
```

Terakhir, Anda juga dapat menambahkan banyak item ke troli sekaligus.
Anda bisa meneruskan metode `add ()` sebuah array array, atau array yang dapat dibeli dan mereka akan ditambahkan ke troli. 

**Saat menambahkan beberapa item ke troli, metode `add ()` akan mengembalikan array CartItems.**

```php
Cart::add([
  ['id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 10.00, 'weight' => 550],
  ['id' => '4832k', 'name' => 'Product 2', 'qty' => 1, 'price' => 10.00, 'weight' => 550, 'options' => ['size' => 'large']]
]);

Cart::add([$product1, $product2]);

```

### Cart::update()

Untuk memperbarui item di troli, Anda harus terlebih dahulu membutuhkan rowId item.
Selanjutnya Anda dapat menggunakan metode `update ()` untuk memperbaruinya.

Jika Anda hanya ingin memperbarui kuantitas, Anda akan melewati metode pembaruan rowId dan kuantitas baru:

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::update($rowId, 2); // Will update the quantity
```
Jika Anda ingin memperbarui lebih banyak atribut dari item, Anda dapat melewati metode pembaruan array atau `Dapat Dibeli` sebagai parameter kedua. Dengan cara ini Anda dapat memperbarui semua informasi item dengan rowId yang diberikan.

```php
Cart::update($rowId, ['name' => 'Product 1']); // Will update the name

Cart::update($rowId, $product); // Will update the id, name and price

```

### Cart::remove()

Untuk menghapus item untuk keranjang, Anda akan membutuhkan rowId lagi. Baris ini. Apakah Anda hanya meneruskan ke metode `hapus ()` dan itu akan menghapus item dari keranjang.

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::remove($rowId);
```

### Cart::get()

Jika Anda ingin mendapatkan item dari troli menggunakan rowId-nya, Anda bisa memanggil metode `get ()` di troli dan meneruskannya dengan rowId.

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::get($rowId);
```

### Cart::content()

Tentu saja Anda juga ingin mendapatkan konten gerobak. Di sinilah Anda akan menggunakan metode `konten`. Metode ini akan mengembalikan Koleksi CartItems yang dapat Anda ulangi dan tampilkan kontennya kepada pelanggan Anda.

```php
Cart::content();
```
Metode ini akan mengembalikan konten instance keranjang saat ini, jika Anda ingin konten instance lain, cukup lakukan panggilan.

```php
Cart::instance('wishlist')->content();
```

### Cart::destroy()

Jika Anda ingin menghapus konten keranjang sepenuhnya, Anda dapat memanggil metode penghancuran di kereta. Ini akan menghapus semua CartItems dari troli untuk instance troli saat ini.

```php
Cart::destroy();
```

### Cart::weight()

Metode `weight ()` dapat digunakan untuk mendapatkan total berat semua item di troli, mengingat berat dan kuantitasnya.


```php
Cart::weight();
```

Metode ini akan memformat hasilnya secara otomatis, yang dapat Anda atur menggunakan tiga parameter opsional

```php
Cart::weight($decimals, $decimalSeperator, $thousandSeperator);
```

Anda dapat mengatur format angka default dalam file konfigurasi.

**Jika Anda tidak menggunakan Facade, tetapi menggunakan injeksi ketergantungan pada Pengontrol Anda (misalnya), Anda juga bisa mendapatkan total properti `$ cart-> weight`**

### Cart::total()

Maka `total ()` dapat digunakan untuk mendapatkan total yang dihitung dari semua item dalam troli, mengingat ada harga dan kuantitas.

```php
Cart::total();
```

Metode ini akan memformat hasilnya secara otomatis, yang dapat Anda atur menggunakan tiga parameter opsional

```php
Cart::total($decimals, $decimalSeparator, $thousandSeparator);
```

Anda dapat mengatur format angka default dalam file konfigurasi.


**Jika Anda tidak menggunakan Facade, tetapi menggunakan injeksi ketergantungan pada Pengontrol Anda (misalnya), Anda juga bisa mendapatkan total properti `$ cart-> total`**

### Cart::tax()

Maka `tax ()` dapat digunakan untuk mendapatkan jumlah pajak yang dihitung untuk semua item di troli, mengingat ada harga dan kuantitas.

```php
Cart::tax();
```

Metode ini akan memformat hasilnya secara otomatis, yang dapat Anda atur menggunakan tiga parameter opsional

```php
Cart::tax($decimals, $decimalSeparator, $thousandSeparator);
```

Anda dapat mengatur format angka default dalam file konfigurasi.

**Jika Anda tidak menggunakan Facade, tetapi menggunakan injeksi ketergantungan pada Pengontrol Anda (misalnya), Anda juga bisa mendapatkan total properti `$ cart-> tax`**

### Cart::subtotal()

Maka `subtotal ()` dapat digunakan untuk mendapatkan total semua item dalam troli, dikurangi jumlah total pajak. 

```php
Cart::subtotal();
```

Metode ini akan memformat hasilnya secara otomatis, yang dapat Anda atur menggunakan tiga parameter opsional

```php
Cart::subtotal($decimals, $decimalSeparator, $thousandSeparator);
```

Anda dapat mengatur format angka default dalam file konfigurasi.

**Jika Anda tidak menggunakan Facade, tetapi menggunakan injeksi ketergantungan pada Pengontrol Anda (misalnya), Anda juga bisa mendapatkan total properti `$ cart-> subtotal`**

### Cart::discount()

Maka `diskon ()` dapat digunakan untuk mendapatkan diskon total semua item di troli.

```php
Cart::discount();
```

Metode ini akan memformat hasilnya secara otomatis, yang dapat Anda atur menggunakan tiga parameter opsional

```php
Cart::discount($decimals, $decimalSeparator, $thousandSeparator);
```

Anda dapat mengatur format angka default dalam file konfigurasi.

**Jika Anda tidak menggunakan Facade, tetapi menggunakan injeksi ketergantungan pada Pengontrol Anda (misalnya), Anda juga bisa mendapatkan total properti `$ cart-> discount`**

### Cart::initial()

maka `initial ()` dapat digunakan untuk mendapatkan harga total semua item di troli sebelum diskon.

```php
Cart::initial();
```

Metode ini akan memformat hasilnya secara otomatis, yang dapat Anda atur menggunakan tiga parameter opsional

```php
Cart::initial($decimals, $decimalSeparator, $thousandSeparator);
```

Anda dapat mengatur format angka default dalam file konfigurasi.

**Jika Anda tidak menggunakan Facade, tetapi menggunakan injeksi ketergantungan pada Pengontrol Anda (misalnya), Anda juga bisa mendapatkan total properti `$ cart-> initial`**

### Cart::count()

Jika Anda ingin tahu berapa banyak item yang ada di troli Anda, Anda dapat menggunakan metode `count ()`. Metode ini akan mengembalikan jumlah total barang dalam kereta. Jadi, jika Anda telah menambahkan 2 buku dan 1 kemeja, itu akan mengembalikan 3 item.

```php
Cart::count();
$cart->count();
```

### Cart::search()

Untuk menemukan item di troli, Anda dapat menggunakan metode `search ()`.

**Metode ini diubah pada versi 2**

Di belakang layar, metode ini hanya menggunakan metode filter dari kelas Laravel Collection. Ini berarti Anda harus memberikannya suatu Penutupan di mana Anda akan menentukan istilah pencarian Anda.

Jika Anda misalnya ingin menemukan semua item dengan id 1:

```php
$cart->search(function ($cartItem, $rowId) {
	return $cartItem->id === 1;
});
```

Seperti yang Anda lihat, Penutupan akan menerima dua parameter. Yang pertama adalah Item Keranjang untuk melakukan pemeriksaan terhadap. Parameter kedua adalah rowId dari CartItem ini.

** Metode ini akan mengembalikan Koleksi yang berisi semua CartItems yang ditemukan **

Cara pencarian ini memberi Anda kontrol total atas proses pencarian dan memberi Anda kemampuan untuk membuat pencarian yang sangat tepat dan spesifik.

### Cart :: setTax ($ rowId, $ taxRate)

Anda dapat menggunakan metode `setTax ()` untuk mengubah tarif pajak yang berlaku untuk CartItem. Ini akan menimpa nilai yang ditetapkan dalam file konfigurasi.

```php
Cart::setTax($rowId, 21);
$cart->setTax($rowId, 21);
```

### Cart::setGlobalTax($taxRate)

Anda dapat menggunakan metode `setGlobalTax ()` untuk mengubah tarif pajak untuk semua item di troli. Item baru juga akan menerima setGlobalTax.

```php
Cart::setGlobalTax(21);
$cart->setGlobalTax(21);
```

### Cart::setGlobalDiscount($discountRate)

Anda dapat menggunakan metode `setGlobalDiscount ()` untuk mengubah tingkat diskonto untuk semua item di troli. Barang baru akan menerima diskon juga.

```php
Cart::setGlobalDiscount(50);
$cart->setGlobalDiscount(50);
```

### Cart::setDiscount($rowId, $taxRate)

Anda dapat menggunakan metode `setDiscount ()` untuk mengubah tingkat diskonto yang menerapkan CartItem. Perlu diingat bahwa nilai ini akan berubah jika Anda menetapkan diskon global untuk Keranjang sesudahnya.

```php
Cart::setDiscount($rowId, 21);
$cart->setDiscount($rowId, 21);
```

### Buyable

Untuk kenyamanan menambahkan item yang lebih cepat ke troli dan asosiasi otomatisnya, model Anda harus mengimplementasikan antarmuka `Dapat Dibeli` Anda dapat menggunakan sifat `CanBeBought` untuk mengimplementasikan metode yang diperlukan tetapi perlu diingat bahwa ini akan menggunakan bidang yang telah ditentukan pada model Anda untuk nilai yang diperlukan.

```php
<?php
namespace App\Models;

use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements Buyable {
    use Gloudemans\Shoppingcart\CanBeBought;
}
```

Jika sifat tidak berfungsi pada model atau Anda tidak dapat memetakan bidang secara manual model harus menerapkan metode antarmuka `Buy Able`. Untuk melakukannya, ia harus mengimplementasikan fungsi-fungsi tersebut:

```php
    public function getBuyableIdentifier(){
        return $this->id;
    }
    public function getBuyableDescription(){
        return $this->name;
    }
    public function getBuyablePrice(){
        return $this->price;
    }
    public function getBuyableWeight(){
        return $this->weight;
    }
```

Contoh:

```php
<?php
namespace App\Models;

use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements Buyable {
    public function getBuyableIdentifier($options = null) {
        return $this->id;
    }
    public function getBuyableDescription($options = null) {
        return $this->name;
    }
    public function getBuyablePrice($options = null) {
        return $this->price;
    }
}
```

## Collections

Dalam beberapa kasus, Keranjang akan mengembalikan kepada Anda Koleksi. Ini hanya Koleksi Laravel sederhana, sehingga semua metode yang dapat Anda panggil pada Koleksi Laravel juga tersedia pada hasilnya.

Sebagai contoh, Anda dapat dengan cepat mendapatkan jumlah produk unik dalam keranjang:

```php
Cart::content()->count();
```

Atau Anda dapat mengelompokkan konten berdasarkan id produk:

```php
Cart::content()->groupBy('id');
```

## Instances

Paket-paket mendukung beberapa instance dari kereta. Cara kerjanya seperti ini:

Anda dapat mengatur instance keranjang saat ini dengan memanggil `Cart :: instance ('newInstance')`. Mulai saat ini, instance aktif dari cart adalah `newInstance`, jadi ketika Anda menambah, menghapus, atau mendapatkan konten dari cart, Anda bekerja dengan instance` newInstance` dari cart.
Jika Anda ingin mengganti instance, Anda cukup memanggil `Cart :: instance ('otherInstance')` lagi, dan Anda bekerja dengan `otherInstance` lagi.

Contoh Kecil:

```php
Cart::instance('shopping')->add('192ao12', 'Product 1', 1, 9.99, 550);

// Get the content of the 'shopping' cart
Cart::content();

Cart::instance('wishlist')->add('sdjk922', 'Product 2', 1, 19.95, 550, ['size' => 'medium']);

// Get the content of the 'wishlist' cart
Cart::content();

// If you want to get the content of the 'shopping' cart again
Cart::instance('shopping')->content();

// And the count of the 'wishlist' cart again
Cart::instance('wishlist')->count();
```

Anda juga dapat menggunakan Kontrak `InstanceIdentifier` untuk memperpanjang Model yang diinginkan untuk menetapkan / membuat instance Cart untuknya. Ini juga memungkinkan untuk secara langsung mengatur diskon global.
```
<?php

namespace App;
...
use Illuminate\Foundation\Auth\User as Authenticatable;
use Gloudemans\Shoppingcart\Contracts\InstanceIdentifier;

class User extends Authenticatable implements InstanceIdentifier
{
	...

	/**
     * Get the unique identifier to load the Cart from
     *
     * @return int|string
     */
    public function getInstanceIdentifier($options = null)
    {
        return $this->email;
    }

    /**
     * Get the unique identifier to load the Cart from
     *
     * @return int|string
     */
    public function getInstanceGlobalDiscount($options = null)
    {
        return $this->discountRate ?: 0;
    }
}

// Inside Controller
$user = \Auth::user();
$cart = Cart::instance($user);



```

**N.B. Ingatlah bahwa troli tetap berada di set instance terakhir selama Anda tidak menyetel yang berbeda selama eksekusi skrip.**

**N.B.2 Contoh cart default disebut `default`, jadi ketika Anda tidak menggunakan instance,` Cart :: konten (); `sama dengan` Cart :: instance ('default') -> konten () `.**

## Models

Karena sangat nyaman untuk dapat secara langsung mengakses model dari CartItem, apakah mungkin untuk mengaitkan model dengan barang-barang di dalam kereta. Katakanlah Anda memiliki model `Produk` di aplikasi Anda. Dengan metode `associate ()`, Anda dapat memberi tahu troli bahwa item di troli, terkait dengan model `Product`. 

Dengan begitu Anda dapat mengakses model Anda langsung dari `CartItem`!

Model ini dapat diakses melalui properti `model` di CartItem.

**Jika model Anda mengimplementasikan antarmuka `Buy Able` dan Anda menggunakan model Anda untuk menambahkan item ke troli, itu akan dikaitkan secara otomatis.**

Berikut adalah contoh:

```php

// First we'll add the item to the cart.
$cartItem = Cart::add('293ad', 'Product 1', 1, 9.99, 550, ['size' => 'large']);

// Next we associate a model with the item.
Cart::associate($cartItem->rowId, 'Product');

// Or even easier, call the associate method on the CartItem!
$cartItem->associate('Product');

// You can even make it a one-liner
Cart::add('293ad', 'Product 1', 1, 9.99, 550, ['size' => 'large'])->associate('Product');

// Now, when iterating over the content of the cart, you can access the model.
foreach(Cart::content() as $row) {
	echo 'You have ' . $row->qty . ' items of ' . $row->model->name . ' with description: "' . $row->model->description . '" in your cart.';
}
```
## Database

- [Config](#configuration)
- [Storing the cart](#storing-the-cart)
- [Restoring the cart](#restoring-the-cart)

### Konfigurasi
Untuk menyimpan keranjang ke dalam basis data sehingga Anda dapat mengambilnya nanti, paket perlu mengetahui koneksi basis data yang digunakan dan apa nama tabelnya.
Secara default paket akan menggunakan koneksi database default dan menggunakan tabel bernama `shoppingcart`.
Jika Anda ingin mengubah opsi ini, Anda harus menerbitkan file `config`.

    php artisan vendor:publish --provider="Gloudemans\Shoppingcart\ShoppingcartServiceProvider" --tag="config"

Ini akan memberi Anda file konfigurasi `cart.php` di mana Anda dapat melakukan perubahan.

Untuk memudahkan hidup Anda, paket ini juga menyertakan `migration` yang siap digunakan yang dapat Anda terbitkan dengan menjalankan:

    php artisan vendor:publish --provider="Gloudemans\Shoppingcart\ShoppingcartServiceProvider" --tag="migrations"
    
Ini akan menempatkan file migrasi tabel `shoppingcart` ke direktori` database / migrations`. Sekarang yang harus Anda lakukan adalah menjalankan `php artisan migrate` untuk memigrasi basis data Anda.

### Menyimpan ke Troli    
Untuk menyimpan instance kereta ke dalam database, Anda harus memanggil metode `store ($ identifier)`. Di mana `$ identifier` adalah kunci acak, misalnya id atau nama pengguna pengguna.

    Cart::store('username');
    
    // To store a cart instance named 'wishlist'
    Cart::instance('wishlist')->store('username');

### Mengembalikan ke Troli
Jika Anda ingin mengambil keranjang dari database dan mengembalikannya, yang harus Anda lakukan adalah memanggil `restore ($ identifier)` di mana `$ identifier` adalah kunci yang Anda tentukan untuk metode` store`.
 
    Cart::restore('username');
    
    // To restore a cart instance named 'wishlist'
    Cart::instance('wishlist')->restore('username');

### Menggabungkan Troli
Jika Anda ingin menggabungkan keranjang dengan keranjang lain dari basis data, yang harus Anda lakukan adalah memanggil `gabungan ($ identifier)` di mana `$ identifier` adalah kunci yang Anda tentukan untuk metode` store`. Anda juga dapat menentukan apakah Anda ingin mempertahankan potongan harga dan tarif pajak item.
     
    // Merge the contents of 'savedcart' into 'username'.
    Cart::instance('username')->merge('savedcart', $keepDiscount, $keepTaxrate);

## Pengecualian

Paket Cart akan mengeluarkan pengecualian jika terjadi kesalahan. Dengan cara ini lebih mudah untuk men-debug kode Anda menggunakan paket Cart atau untuk menangani kesalahan berdasarkan pada jenis pengecualian. Paket-paket Cart dapat membuang pengecualian berikut:

| Exception                    | Reason                                                                             |
| ---------------------------- | ---------------------------------------------------------------------------------- |
| *CartAlreadyStoredException* | Saat mencoba menyimpan keranjang yang sudah disimpan menggunakan pengenal yang ditentukan |
| *InvalidRowIDException*      | Ketika rowId yang diteruskan tidak ada dalam instance troli saat ini         |
| *UnknownModelException*      | Saat Anda mencoba mengaitkan model yang tidak ada dengan Item Keranjang.                    |

## Events

Troli juga memiliki event. Ada lima event yang bisa Anda lakukan.

| Event         | Fired                                    | Parameter                        |
| ------------- | ---------------------------------------- | -------------------------------- |
| cart.added    | Saat item ditambahkan ke troli.          | The `CartItem` that was added.   |
| cart.updated  | Ketika item dalam troli diperbarui.      | The `CartItem` that was updated. |
| cart.removed  | Ketika item dalam troli dihapus.         | The `CartItem` that was removed. |
| cart.stored   | Ketika isi trol disimpan.                | -                                |
| cart.restored | Ketika konten keranjang Dikembalikan.    | -                                |

## Contoh

Di bawah ini adalah sedikit contoh cara membuat daftar isi keranjang dalam sebuah tabel:

```php

// Tambahkan beberapa item di Kontroler Anda.
Cart::add('192ao12', 'Product 1', 1, 9.99);
Cart::add('1239ad0', 'Product 2', 2, 5.95, ['size' => 'large']);

// Tampilkan konten dalam Tampilan.
<table>
   	<thead>
       	<tr>
           	<th>Product</th>
           	<th>Qty</th>
           	<th>Price</th>
           	<th>Subtotal</th>
       	</tr>
   	</thead>

   	<tbody>

   		<?php foreach(Cart::content() as $row) :?>

       		<tr>
           		<td>
               		<p><strong><?php echo $row->name; ?></strong></p>
               		<p><?php echo ($row->options->has('size') ? $row->options->size : ''); ?></p>
           		</td>
           		<td><input type="text" value="<?php echo $row->qty; ?>"></td>
           		<td>$<?php echo $row->price; ?></td>
           		<td>$<?php echo $row->total; ?></td>
       		</tr>

	   	<?php endforeach;?>

   	</tbody>
   	
   	<tfoot>
   		<tr>
   			<td colspan="2">&nbsp;</td>
   			<td>Subtotal</td>
   			<td><?php echo Cart::subtotal(); ?></td>
   		</tr>
   		<tr>
   			<td colspan="2">&nbsp;</td>
   			<td>Tax</td>
   			<td><?php echo Cart::tax(); ?></td>
   		</tr>
   		<tr>
   			<td colspan="2">&nbsp;</td>
   			<td>Total</td>
   			<td><?php echo Cart::total(); ?></td>
   		</tr>
   	</tfoot>
</table>
```
