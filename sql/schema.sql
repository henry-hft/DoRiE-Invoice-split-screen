CREATE TABLE `invoices` (
				`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
				`seat` int(1) NOT NULL,
				`status` varchar(255) NOT NULL,
				`paid` int(1) NOT NULL DEFAULT 0,
				`requested` varchar(31) DEFAULT '0' NOT NULL,
				`time` varchar(31) NOT NULL
				);
				
CREATE TABLE `orders` (
				`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
				`invoiceid` int(11) NOT NULL,
				`productid` varchar(11) NOT NULL,
				`price` decimal(11,2) NOT NULL,
				`time` varchar(31) NOT NULL
				);
				
CREATE TABLE `products` (
				`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
				`name` varchar(255) NOT NULL,
				`description` varchar(255) NOT NULL,
				`price` decimal(11,2) NOT NULL,
				`stock` int(11) NOT NULL,
				`available` int(1) NOT NULL DEFAULT 1
				);				
				
CREATE TABLE `events` (
				`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
				`seat` int(1) NOT NULL,
				`image` varchar(255) NOT NULL,
				`text` varchar(255) NOT NULL,
				`duration` varchar(11) NOT NULL
  				);
				
INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `available`) VALUES
				(1, 'Drink', 'Cola 0,33l', '2.30', 100, 1),
				(2, 'Snack', 'Salzstangen', '1.10', 100, 1);
