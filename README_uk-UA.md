## LaravelShoppingcart
[![Build Status](https://travis-ci.org/bumbummen99/LaravelShoppingcart.png?branch=master)](https://travis-ci.org/bumbummen99/LaravelShoppingcart)
[![codecov](https://codecov.io/gh/bumbummen99/LaravelShoppingcart/branch/master/graph/badge.svg)](https://codecov.io/gh/bumbummen99/LaravelShoppingcart)
[![StyleCI](https://styleci.io/repos/152610878/shield?branch=master)](https://styleci.io/repos/152610878)
[![Total Downloads](https://poser.pugx.org/bumbummen99/shoppingcart/downloads.png)](https://packagist.org/packages/bumbummen99/shoppingcart)
[![Latest Stable Version](https://poser.pugx.org/bumbummen99/shoppingcart/v/stable)](https://packagist.org/packages/bumbummen99/shoppingcart)
[![Latest Unstable Version](https://poser.pugx.org/bumbummen99/shoppingcart/v/unstable)](https://packagist.org/packages/bumbummen99/shoppingcart)
[![License](https://poser.pugx.org/bumbummen99/shoppingcart/license)](https://packagist.org/packages/bumbummen99/shoppingcart)

Цей репозиторій є відгалуженням [Crinsane's LaravelShoppingcart](https://github.com/Crinsane/LaravelShoppingcart) та містить додаткові незначні доповнення, сумісні з Laravel 6.

## Встановлення

Встановіть [пакет](https://packagist.org/packages/bumbummen99/shoppingcart) скориставшись [Завантажувачем](http://getcomposer.org/). 

Для запуску Завантажувача, скористайтеся командою у Терміналі:

    composer require bumbummen99/shoppingcart

Тепер ви готові розпочати користуватися кошиком у вашому застосунку.

**Починаючи з версії 2 даного пакету з'явилася можливість впровадження залежності для впровадження екземпляра класу Кошик (Cart) до вашого контролера або іншого класу**

## Огляд
Щоб детальніше ознайомитися LaravelShoppingcart, можете пройти за посиланнями

* [Застосування](#usage)
* [Колекції](#collections)
* [Екземпляри](#instances)
* [Моделі](#models)
* [База даних](#database)
* [Винятки](#exceptions)
* [Події](#events)
* [Приклад](#example)

## Застосування

Кошик (Cart) дозволяє вам скористатися наступними методами:

### Cart::add()

Додавати покупки у кошик дуже зручно - достатньо скористатися методом `add()`, який приймає різноманітні параметри.

У найпростішій формі метода достатньо вказати ідентифікатор товару, назву, кількість, ціну та вагу товару, який ви хочете додати у кошик.

```php
Cart::add('293ad', 'Product 1', 1, 9.99, 550);
```

У якості додаткового п'ятого параметра можна задати додаткові опції, наприклад, щоб додати декілька одиниць з однаковим ідентифікатором, але, наприклад, різного розміру.

```php
Cart::add('293ad', 'Product 1', 1, 9.99, 550, ['size' => 'large']);
```

**Метод `add()` повертає екземпляр CartItems того товару, який ви щойно додали у кошик.**

Можливо, вам більше до вподоби додавати товари, використовуючи масив? Якщо масив містить усі необхідні поля, ви можете передавати масив у цей метод. Поле із додатковими опціями є необов'язковим.

```php
Cart::add(['id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 9.99, 'weight' => 550, 'options' => ['size' => 'large']]);
```

У версії 2 пакета з'явилася нова можливість для роботи з інтерфейсом [Buyable](#buyable). Такий функціонал з'являється за рахунок того, що модель запускає інтерфейс [Buyable](#buyable), який дозволить імплементувати декілька методів, з яких пакет знатиме як отримати ідентифікатор, назву та ціну з вашої моделі. 
Таким чином, ви можете передати метод `add()` та кількість одиниць товару до моделі, а вона автоматично додасть їх до кошика. 

**Додатковий бонус інтерфейсу - автоматичне об'єднання моделі з CartItems**

```php
Cart::add($product, 1, ['size' => 'large']);
```
У якості додаткового параметра, ви можете внести опції.
```php
Cart::add($product, 1, ['size' => 'large']);
```

Нарешті, ви також можете додавати до кошика декілька одиниць водночас. Для цього потрібно передати у `add()` масив масивів або масив Buyables, і їх буде додано в кошик. 

**Під час додавання декількох одиниць товару в кошик, метод `add()` повертає масив CartItems.**

```php
Cart::add([
  ['id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 10.00, 'weight' => 550],
  ['id' => '4832k', 'name' => 'Product 2', 'qty' => 1, 'price' => 10.00, 'weight' => 550, 'options' => ['size' => 'large']]
]);

Cart::add([$product1, $product2]);

```

### Cart::update()

Щоб оновити товар у кошику, вам знадобиться ідентифікатор рядка (rowId) даного товару.
Далі ви можете скористатися методом `update()` для того, щоб оновити його.

Якщо ви просто хочете оновити кількість товару, вам необхідно передати у метод `update()` rowId і оновлену кількість:

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::update($rowId, 2); // Will update the quantity
```

Якщо ви хочете оновити більше атрибутів товару, вам потрібно або передати у метод `update()` масив або `Buyable` у якості другого параметра. Таким чином, ви можете оновити всю інформацію про товар за заданим rowId.

```php
Cart::update($rowId, ['name' => 'Product 1']); // Will update the name

Cart::update($rowId, $product); // Will update the id, name and price

```

### Cart::remove()

Щоб вилучити товар з кошика, вам знову знадобиться rowId. Такий rowId потрібно передати у метод `remove()`, який автоматично вилучить товар із кошика.

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::remove($rowId);
```

### Cart::get()

Якщо ви хочете отримати товар із кошика, використовуючи його rowId, вам потрібно застосувати метод `get()` щодо кошика і передати в нього rowId.

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::get($rowId);
```

### Cart::content()

Вам також може знадобитися можливість отримати інформацію про вміст кошика. Для цього вам потрібно скористатися методом `content`. Такий метод повертає колекцію CartItems, ви можете перебирати вміст такої колекції і відобразити вміст кошика для ваших клієнтів.

```php
Cart::content();
```

Даний метод повертає вміст поточного екземпляра кошика, якщо ви хочете вміст іншого екземпляра, вам потрібно зв'язати виклики.

```php
Cart::instance('wishlist')->content();
```

### Cart::destroy()

Якщо ви хочете остаточно вилучити вміст кошика, ви можете застосувати метод `destroy()` щодо кошика. Даний метод вилучить всі CartItems з кошика для поточного екземпляра кошика.

```php
Cart::destroy();
```

### Cart::weight()

Метод `weight()` можна застосувати, щоб отримати розрахунок ваги усіх товарів у кошику, за умови, що задано вагу і кількість одиниць.

```php
Cart::weight();
```

Даний метод автоматично відформатує результат, який ви можете поправити за допомогою трьох додаткових параметрів.

```php
Cart::weight($decimals, $decimalSeperator, $thousandSeperator);
```

Ви можете задати формат чисел за замовчуванням у файлі з конфігураціями.

**Якщо ви не використовуєте Фасад, але застосовуєте впровадження залежності, наприклад, у вашому Контролері, ви також можете отримати інформацію про вагу товарів через `$cart->weight`**

### Cart::total()

Метод `total()` можна застосовувати, щоб отримати розрахунок вартості усіх товарів у кошику, за умови, що задані ціна і кількість одиниць.

```php
Cart::total();
```

Даний метод автоматично відформатує результат, який ви можете поправити за допомогою трьох додаткових параметрів.

```php
Cart::total($decimals, $decimalSeparator, $thousandSeparator);
```

Ви можете задати формат чисел за замовчуванням у файлі з конфігураціями.

**Якщо ви не використовуєте Фасад, але застосовуєте впровадження залежності, наприклад, у вашому Контролері, ви також можете отримати інформацію про вартість товарів через `$cart->total`**

### Cart::tax()

Метод `tax()` можна застосовувати, щоб отримати розрахунок суми податків для усіх товарів у кошику, за умови, що задані ціна і кількість одиниць.

```php
Cart::tax();
```

Даний метод автоматично відформатує результат, який ви можете поправити за допомогою трьох додаткових параметрів.

```php
Cart::tax($decimals, $decimalSeparator, $thousandSeparator);
```

Ви можете задати формат чисел за замовчуванням у файлі з конфігураціями.

**Якщо ви не використовуєте Фасад, але застосовуєте впровадження залежності, наприклад, у вашому Контролері, ви також можете отримати інформацію про суму податку на товари через `$cart->tax`**

### Cart::subtotal()

Метод `subtotal()` можна застосовувати, щоб отримати розрахунок вартості усіх товарів у кошику, без урахування суми податку. 

```php
Cart::subtotal();
```

Даний метод автоматично відформатує результат, який ви можете поправити за допомогою трьох додаткових параметрів.

```php
Cart::subtotal($decimals, $decimalSeparator, $thousandSeparator);
```

Ви можете задати формат чисел за замовчуванням у файлі з конфігураціями.

**Якщо ви не використовуєте Фасад, але застосовуєте впровадження залежності, наприклад, у вашому Контролері, ви також можете отримати інформацію про вартість усіх товарів без урахування суми податків через `$cart->subtotal`**

### Cart::discount()

Метод `discount()` можна застосовувати, щоб отримати розрахунок знижки на усі товари у кошику. 

```php
Cart::discount();
```

Даний метод автоматично відформатує результат, який ви можете поправити за допомогою трьох додаткових параметрів.

```php
Cart::discount($decimals, $decimalSeparator, $thousandSeparator);
```

Ви можете задати формат чисел за замовчуванням у файлі з конфігураціями.

**Якщо ви не використовуєте Фасад, але застосовуєте впровадження залежності, наприклад, у вашому Контролері, ви також можете отримати інформацію про вартість усіх товарів з урахуванням знижки `$cart->discount`**

### Cart::initial()

Метод `initial()` можна застосовувати, щоб отримати розрахунок вартості усіх товарів до застосування знижки. 

```php
Cart::initial();
```

Даний метод автоматично відформатує результат, який ви можете поправити за допомогою трьох додаткових параметрів.

```php
Cart::initial($decimals, $decimalSeparator, $thousandSeparator);
```

Ви можете задати формат чисел за замовчуванням у файлі з конфігураціями.

**Якщо ви не використовуєте Фасад, але застосовуєте впровадження залежності, наприклад, у вашому Контролері, ви також можете отримати інформацію про вартість усіх товарів до застосування знижки `$cart->initial`**

### Cart::count()

Метод `count()` можна застосовувати, щоб дізнатися кількість одиниць товарів у кошику. Даний метод повертає загальну кількість одиниць товарів у кошику. Тобто якщо ви додали 2 книжки і 1 сорочку, цей метод поверне 3 одиниці.

```php
Cart::count();
$cart->count();
```

### Cart::search()

Метод `search()` можна застосовувати, щоб знайти одиницю товару у кошику.

**Даний метод було змінено у версії 2**

У своїй імплементації, цей метод застосовує метод фільтрування з класу Laravel Collection. Це означає, що вам потрібно передати замикання (Closure) для даного методу, де ви зазначите умови для пошуку.

Наприклад, якщо ви хочете знайти всі одиниці товару з ідентифікатором 1:

```php
$cart->search(function ($cartItem, $rowId) {
	return $cartItem->id === 1;
});
```

Як ви можете побачити, замикання отримає 2 параметра. Перший - CartItem для здійснення перевірки щодо нього. Другий параметр - rowId даного CartItem.

**Даний метод повертає колекцію, яка вміщує всі CartItems, які було знайдено**

Такий спосіб пошуку надає вам повний контроль над процесом пошуку та дозволяє здійснювати дуже точні та конкретні пошуки.

### Cart::setTax($rowId, $taxRate)

Метод `setTax()` можна застосовувати, щоб змінювати ставку оподаткування, яка застосовується до CartItem. Така операція перезапише значення встановлене у файлі з конфігураціями.

```php
Cart::setTax($rowId, 21);
$cart->setTax($rowId, 21);
```

### Cart::setGlobalTax($taxRate)

Метод `setGlobalTax()` можна застосовувати, щоб змінити ставку оподаткування для усіх найменувать у кошику. Нові найменування отримають значення setGlobalTax також.

```php
Cart::setGlobalTax(21);
$cart->setGlobalTax(21);
```

### Cart::setGlobalDiscount($discountRate)

Метод `setGlobalDiscount()` можна застосовувати для заміни ставки знижки щодо усіх найменувань у кошику. Нові найменування також отримуватимуть таку знижку.

```php
Cart::setGlobalDiscount(50);
$cart->setGlobalDiscount(50);
```

### Cart::setDiscount($rowId, $taxRate)

Застосування методу `setDiscount()` полягає у заміні ставки знижки, яка застосовується до CartItem. Зверніть увагу, що дане значення ставки знижки буде змінено, якщо ви згодом встановите глобальну знижку для Кошика (Cart).

```php
Cart::setDiscount($rowId, 21);
$cart->setDiscount($rowId, 21);
```

### Buyable

Для зручного швидкого додавання товарів до кошика та їхнього автоматичного об'єднання, ваша модель повинна запустити інтерфейс  `Buyable`. Ви можете застосовувати `CanBeBought` трейт для імплементації необхідних методів, але зверніть увагу, що такі методи застосовуватимуть попередньо визначені поля у вашій моделі для необхідних значень.
```php
<?php
namespace App\Models;

use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements Buyable {
    use Gloudemans\Shoppingcart\CanBeBought;
}
```

Якщо трейт не працює на вашій моделі або ви хочете вручну перенести (мапувати) поля, модель повинна запустити методи інтерфейсу `Buyable`. Для цього, модель повинна імплементувати наступні функції:

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

Приклад:

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

## Колекції

Щодо багатьох екземплярів Кошик (Cart) повертає Колекцію, яка є простим видом Laravel Collection. Таким чином усі методи, які ви можете застосовувати щодо Laravel Collection, є також доступними у результаті операції.

Наприклад, ви можете швидко отримати кількість унікальних товарів у кошику:

```php
Cart::content()->count();
```

Або групувати вміст за ідентифікатором товару:

```php
Cart::content()->groupBy('id');
```

## Екземпляри

Пакет підтримує декілька екземплярів кошика. Як це працює:

Ви можете встановити поточний екземпляр кошика через виклик `Cart::instance('newInstance')`. З цього моменту, активний екземляр кошика буде `newInstance`, тому коли ви додаєте, вилучаєте або отримуєте інформацію щодо вмісту кошика, ви працюєте з екземпляром `newInstance` кошика.
Якщо ви хочете переключитися між екзмеплярами, ви можете викликати `Cart::instance('otherInstance')` ще раз, і ви знову працюватимете з `otherInstance`.

Короткий приклад:

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

Ви також можете застосувати Контракт `InstanceIdentifier` для розширення бажаної моделі через призначення / створення екземпляру Кошика (Cart) для неї. Така дія також дозволить напряму встановлювати глобальну знижку.
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

**N.B. Зверніть увагу, що кошик залишається у стані останнього призначеного екземпляра, доки ви не встановите інший екземпляр протягом виконання скрипта.**

**N.B.2 За замовчуванням екземпляр кошика називається `default`, тому коли ви не використовуєте екземпляри, `Cart::content();` залишається таким самим як і `Cart::instance('default')->content()`.**

## Моделі

Через те, що можливість прямого доступу до моделі з CartItem може бути дуже зручною, виникає питання чи можливо об'єднати модель із товарами у кошику. Скажімо, у вашому застосунку є модель `Product`. Завдяки методу `associate()` ви можете вказати кошику, що товар у кошику об'єднаний з моделлю `Product`. 

Таким чином ви можете отримати доступ до вашої моделі одразу з `CartItem`!

Доступ до моделі також можна отримати через властивість CartItem `model`.

**Якщо ваша модель запускає інтерфейс `Buyable` і ви використовували вашу модель для додавання товару до кошика, вони будуть об'єднані автоматично.**

Ось приклад:

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
## База даних

- [Конфігурації](#configuration)
- [Збереження кошика](#storing-the-cart)
- [Відновлення кошика](#restoring-the-cart)

### Конфігурації
Для збереження кошика до бази даних, щоб ви могли отримати його пізніше, пакет повинен знати яке підключення до бази даних використовувати і яка назва окремої таблиці.
За замовчуванням, пакет використовуватиме підключення до бази даних, яке вказане за замовчуванням, та використовуватиме таблицію `shoppingcart`.
Якщо ви хочете змінити ці значення, вам потрібно буде опублікувати файл з конфігураціями `config`.

    php artisan vendor:publish --provider="Gloudemans\Shoppingcart\ShoppingcartServiceProvider" --tag="config"

Така дія створить вам файл з конфігураціями `cart.php`, в якому ви можете внести бажані зміни.

Щоб спростити ваше життя, пакет також включає готову до вжитку `migration`, яку можна опублікувати через запуск наступної команди:

    php artisan vendor:publish --provider="Gloudemans\Shoppingcart\ShoppingcartServiceProvider" --tag="migrations"
    
Така дія розмістить файл з міграцією таблиці `shoppingcart` в директорію `database/migrations`. Все що вам залишається зробити, це запустити `php artisan migrate` для міграції вашої бази даних.

### Збереження кошика    
Для збереження екземпляра кошика до бази даних, вам потрібно викликати метод `store($identifier) `. Де `$identifier` є випадковим ключем, наприклад, ідентифікатор або ім'я користувача.

    Cart::store('username');
    
    // To store a cart instance named 'wishlist'
    Cart::instance('wishlist')->store('username');

### Відновлення кошика
Якщо ви хочете отримати кошик із бази даних і відновити його, вам знадобиться викликати метод `restore($identifier)`, де `$identifier` - це ключ, який ви зазначили у методі `store`.
 
    Cart::restore('username');
    
    // To restore a cart instance named 'wishlist'
    Cart::instance('wishlist')->restore('username');

### Злиття кошиків
Якщо ви хочете злити кошик із іншим кошиком, збереженим у базі даних, вам знадобиться викликати метод `merge($identifier)`, де `$identifier` - це ключ, який ви зазначили у методі`store`. Ви також можете визначити чи хочете ви зберегти знижку і ставку оподаткування для товарів.
     
    // Merge the contents of 'savedcart' into 'username'.
    Cart::instance('username')->merge('savedcart', $keepDiscount, $keepTaxrate);

## Перехоплення

Пакет Кошик (Cart) видаватиме винятки/перехоплення у разі, якщо щось йде не за планом. Таким чином, вам буде простіше відлагоджувати (debug) ваш код, використовуючи пакет Кошик, або обробляти помилку за типом перехоплення. Пакети Кошика можуть видавати наступні перехоплення:

| Перехоплення                    | Пояснення                                                                             |
| ---------------------------- | ---------------------------------------------------------------------------------- |
| *CartAlreadyStoredException* | ПерехопленняКошикВжеЗбережено Коли ви намагаєтеся зберегти кошик, який вже було збережено, застосовуючи вказаний ідентифікатор |
| *InvalidRowIDException*      | ПерехопленняНеправильнийІдРядка Коли rowId, який було передано, не існує у поточному екземплярі кошика         |
| *UnknownModelException*      | ПерехопленняНевідомаМодель Коли ви намагаєтеся об'єднати неіснуючу модель з CartItem.                    |

## Події

Кошик також має вбудовані події. Існує п'ять подій, які можна очікувати.

| Подія         | Видано                                    | Параметр                        |
| ------------- | ---------------------------------------- | -------------------------------- |
| cart.added    | Коли товар додано до кошика.      | `CartItem`, який було додано.   |
| cart.updated  | Коли товар оновлено у кошику.    | `CartItem`, який було оновлено. |
| cart.removed  | Коли товар вилучено з кошика.   | `CartItem`, який було вилучено. |
| cart.stored   | Коли вміст кошика було збережено.   | -                                |
| cart.restored | Коли вміст кошика було відновлено. | -                                |

## Приклад

Нижче наведено приклад як відобразити вміст кошика у таблиці:

```php

// Add some items in your Controller.
Cart::add('192ao12', 'Product 1', 1, 9.99);
Cart::add('1239ad0', 'Product 2', 2, 5.95, ['size' => 'large']);

// Display the content in a View.
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
