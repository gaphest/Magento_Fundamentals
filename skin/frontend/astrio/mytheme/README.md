# Сборка фронт-енда (альфа)

### Вступление
Весь фронт-енд темы построен на дефолтной теме RWD от Magento. Работа с этой темой будет осуществляться при помощи современных сборщиков и менеджеров пакетов(gulp, bower).

### Начало
Перед началом работы над проектом нужно убедиться, что на компьютере установлены следующие программы:

  - Ruby
  - nodejs
  - Compass

Так же перед началом работы рекомендую ознакомиться [со статьей на официальном сайте], которая посвящена работе с rwd темой. 

### Установка gulp, bower
Gulp - сборщик проектов. Bower - менеджер пакетов. Они нам понадобятся для сборки фронтенда.

Переходим в папку skin
```sh
$ cd skin/frontend/astrio/default
```
Ставим gulp
```sh
$ npm install --global gulp
```
Ставим bower
```sh
$ npm install -g bower
```
### Разворачивание и сборка проекта
Перед сборкой необходимо скопировать файлы стилей и картинки к нам в тему. Делаем это так:
```sh
$ cd [your Magento install dir]
$ cp -R skin/frontend/rwd/default/images skin/frontend/astrio/default
$ cp -R skin/frontend/rwd/default/scss skin/frontend/astrio/default
```
Данная команда установит все пакеты и зависимости в наш проект.
```sh
$ npm install
```
Теперь когда всё готово запускаем сборщик командой:
```sh
$ gulp
```
В результате происходит сборка все файлов стилей.

В процессе работы используем команду, которая будет отслеживать изменения в файлах и собирать проект "на лету"
```sh
$ gulp watch-all
```

При коммите изменений нужно пересобирать проект с использованием сжатия и без маппинга. Чтобы собрать всё для продакшена используем:
```sh
$ gulp --production
```

### Версия документа
0.0.1

### Todo's

 - Описать подробнее все процессы установки
 - Описать подводные камни
 - Описать особенности разворачивания для EE
 - Заполнить файл пакетов
 - Пересмотреть gulpfile для более удобного пользования
 - Перейти на yeoman

License
----

MIT
[со статьей на официальном сайте]:http://www.magentocommerce.com/knowledge-base/entry/ee114-ce19-rwd-dev-guide