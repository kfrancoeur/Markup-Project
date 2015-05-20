CREATE DATABASE MarkupProject;
USE MarkupProject;

CREATE TABLE IF NOT EXISTS Scores (
  `RunID` INT NOT NULL AUTO_INCREMENT,
  `PrefixID` VARCHAR(20) NOT NULL,
  `DateRun` DATE NOT NULL,
  `TimeRun` TIME NOT NULL,
  `FileDate` DATE NOT NULL,
  `Score` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`RunID`)
);
