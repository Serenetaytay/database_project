-- 設定編碼
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- STORE (商店)
DROP TABLE IF EXISTS `STORE`;
CREATE TABLE STORE (
  storeID int(11) NOT NULL AUTO_INCREMENT,
  storeName varchar(50) NOT NULL,
  address varchar(100) DEFAULT NULL,
  Phone varchar(20) DEFAULT NULL,
  worktime varchar(50) DEFAULT NULL,
  PRIMARY KEY (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO STORE (`storeName`, `address`, `Phone`, `worktime`) VALUES
('台北信義店', '台北市信義區松壽路1號', '02-12345678', '09:00-22:00'),
('台中逢甲店', '台中市西屯區逢甲路10號', '04-23456789', '10:00-23:00'),
('高雄巨蛋店', '高雄市左營區博愛二路', '07-34567890', '11:00-21:00');

-- SPECIE (物種)
DROP TABLE IF EXISTS `SPECIE`;
CREATE TABLE SPECIE (
  sID int(11) NOT NULL AUTO_INCREMENT,
  sName varchar(50) NOT NULL,
  PRIMARY KEY (`sID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO SPECIE (`sName`) VALUES ('狗'), ('貓'), ('兔子');

-- BREED (品種)
DROP TABLE IF EXISTS `BREED`;
CREATE TABLE BREED (
  bID int(11) NOT NULL AUTO_INCREMENT,
  sID int(11) NOT NULL,
  bName varchar(50) NOT NULL,
  PRIMARY KEY (`bID`),
  KEY sID (`sID`),
  CONSTRAINT breed_ibfk_1 FOREIGN KEY (`sID`) REFERENCES SPECIE (`sID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO BREED (`sID`, `bName`) VALUES
(1, '黃金獵犬'), (1, '柴犬'), (1, '柯基'),
(2, '美國短毛貓'), (2, '曼赤肯'),
(3, '獅子兔');

-- PET (寵物)
DROP TABLE IF EXISTS `PET`;
CREATE TABLE PET (
  petID int(11) NOT NULL AUTO_INCREMENT,
  bID int(11) NOT NULL,
  storeID int(11) NOT NULL,
  birth date DEFAULT NULL,
  sex enum('公','母') DEFAULT '公',
  personality varchar(255) DEFAULT NULL,
  status varchar(20) DEFAULT '在店',
  petprice int(11) DEFAULT 0,
  PRIMARY KEY (`petID`),
  KEY bID (`bID`),
  KEY storeID (`storeID`),
  CONSTRAINT pet_ibfk_1 FOREIGN KEY (`bID`) REFERENCES BREED (`bID`),
  CONSTRAINT pet_ibfk_2 FOREIGN KEY (`storeID`) REFERENCES STORE (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO PET (`bID`, `storeID`, `birth`, `sex`, `personality`, `status`, `petprice`) VALUES
(1, 1, '2023-01-01', '公', '活潑好動，親人', '在店', 25000),
(2, 1, '2023-02-15', '母', '有點害羞，愛吃', '在店', 18000),
(4, 2, '2023-03-10', '公', '黏人，會呼嚕', '已預約', 12000),
(5, 3, '2023-04-05', '母', '腿短可愛', '在店', 30000);

-- PRODUCT (商品)
DROP TABLE IF EXISTS `PRODUCT`;
CREATE TABLE PRODUCT (
  pID int(11) NOT NULL AUTO_INCREMENT,
  pName varchar(100) NOT NULL,
  storeID int(11) NOT NULL,
  stock int(11) DEFAULT 0,
  PRIMARY KEY (`pID`),
  KEY storeID (`storeID`),
  CONSTRAINT product_ibfk_1 FOREIGN KEY (`storeID`) REFERENCES STORE (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO PRODUCT (`pName`, `storeID`, `stock`) VALUES
('皇家飼料 K36', 1, 50),
('貓抓板', 1, 20),
('狗狗潔牙骨', 2, 100);

-- RESERVE (預約)
DROP TABLE IF EXISTS `RESERVE`;
CREATE TABLE RESERVE (
  rID int(11) NOT NULL AUTO_INCREMENT,
  petID int(11) NOT NULL,
  rName varchar(50) NOT NULL,
  rPhone varchar(20) NOT NULL,
  time datetime NOT NULL,
  status varchar(20) DEFAULT '待確認',
  PRIMARY KEY (`rID`),
  KEY petID (`petID`),
  CONSTRAINT reserve_ibfk_1 FOREIGN KEY (`petID`) REFERENCES PET (`petID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO RESERVE (`petID`, `rName`, `rPhone`, `time`, `status`) VALUES
(3, '王小明', '0912345678', '2023-12-25 14:00:00', '已確認');

SET FOREIGN_KEY_CHECKS = 1;


--添加照片的部分
這跟之前的商店 (Store) 和商品 (Product) 做法完全一樣！我們需要修改資料庫欄位，然後更新程式碼。

請依照以下兩個步驟操作：

第一步：修改資料庫 (新增欄位)
請到 phpMyAdmin，點選你的資料庫 (petshop_db)，點選 SQL 分頁，執行這行指令：

SQL

ALTER TABLE PET ADD COLUMN petImage VARCHAR(255) DEFAULT NULL;

ALTER TABLE PRODUCT ADD COLUMN pImage VARCHAR(255) DEFAULT NULL;

ALTER TABLE STORE ADD COLUMN storeImage VARCHAR(255) DEFAULT NULL;



-- 訂單表 (包含商品名稱和取貨時間)
CREATE TABLE `ORDERS` (
  `orderID` int(11) NOT NULL AUTO_INCREMENT,
  `customerName` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `totalAmount` int(11) NOT NULL,
  `deliveryMethod` varchar(20) DEFAULT '店面自取',
  `pickupTime` datetime DEFAULT NULL,
  `productName` varchar(100) DEFAULT NULL,
  `orderDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`orderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;