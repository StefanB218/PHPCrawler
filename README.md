# PHPCrawler

Dieser Crawler liest Verlinkungen, Telefonnummern und Email-Adressen von einer Website aus und speichert sie in eine Datenbank (MySQL) ab.

Die Datenbank muss vorher in SQL erstellt werden:

```mysql
CREATE DATABASE `phpcrawlerdb`;
```

```mysql
CREATE TABLE `crawler` (
  `result` text NOT NULL,
  `hostname` text NOT NULL,
  `typ` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
