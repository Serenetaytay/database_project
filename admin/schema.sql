-- 設定編碼
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


--  建立 PRODUCT (商品)
DROP TABLE IF EXISTS `PRODUCT`;
CREATE TABLE `PRODUCT` (
  `pID` int(11) NOT NULL AUTO_INCREMENT,
  `pName` varchar(100) NOT NULL,
  `storeID` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0,
  PRIMARY KEY (`pID`),
  KEY `storeID` (`storeID`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`storeID`) REFERENCES `STORE` (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 假資料 PRODUCT
INSERT INTO `PRODUCT` (`pName`, `storeID`, `stock`) VALUES
('皇家飼料 K36', 1, 50),
('貓抓板', 1, 20),
('狗狗潔牙骨', 2, 100);


--  建立 STORE (商店)
DROP TABLE IF EXISTS `STORE`;
CREATE TABLE `STORE` (
  `storeID` int(11) NOT NULL AUTO_INCREMENT,
  `storeName` varchar(50) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `worktime` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 假資料 STORE
INSERT INTO `STORE` (`storeName`, `address`, `Phone`, `worktime`) VALUES
('台北信義店', '台北市信義區松壽路1號', '02-12345678', '09:00-22:00'),
('台中逢甲店', '台中市西屯區逢甲路10號', '04-23456789', '10:00-23:00'),
('高雄巨蛋店', '高雄市左營區博愛二路', '07-34567890', '11:00-21:00');
