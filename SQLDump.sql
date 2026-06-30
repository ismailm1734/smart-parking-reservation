-- =====================================================================
-- CS306 Phase 4 — Smart Parking Reservation Database System
-- SQL Dump: schema + mock data + trigger + stored procedure
-- Author: İsmail Memiş
-- Database: parking_db2 (MariaDB 10.4 / MySQL 8)
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `parking_db2`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `parking_db2`;

-- ---------------------------------------------------------------------
-- Drop existing objects (safe re-run)
-- ---------------------------------------------------------------------
DROP TRIGGER   IF EXISTS trg_prevent_overlap_reservation;
DROP PROCEDURE IF EXISTS sp_GetDriverReservationStats;
DROP TABLE     IF EXISTS `RESERVATION`;
DROP TABLE     IF EXISTS `PARKING_SPOT`;
DROP TABLE     IF EXISTS `PARKING_LOT`;
DROP TABLE     IF EXISTS `DRIVER`;

-- =====================================================================
-- TABLE DEFINITIONS
-- =====================================================================

CREATE TABLE `DRIVER` (
  `driver_id` INT(11)      NOT NULL,
  `name`      VARCHAR(100) NOT NULL,
  `phone`     VARCHAR(20)  NOT NULL,
  `email`     VARCHAR(100) NOT NULL,
  PRIMARY KEY (`driver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `PARKING_LOT` (
  `lot_id`   INT(11)      NOT NULL,
  `lot_name` VARCHAR(100) NOT NULL,
  `capacity` INT(11)      NOT NULL,
  PRIMARY KEY (`lot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `PARKING_SPOT` (
  `spot_id`   INT(11)     NOT NULL,
  `spot_type` VARCHAR(50) NOT NULL,
  `status`    VARCHAR(30) NOT NULL,
  `lot_id`    INT(11)     NOT NULL,
  PRIMARY KEY (`spot_id`),
  KEY `lot_id` (`lot_id`),
  CONSTRAINT `parking_spot_ibfk_1`
    FOREIGN KEY (`lot_id`) REFERENCES `PARKING_LOT` (`lot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `RESERVATION` (
  `reservation_id` INT(11)     NOT NULL,
  `start_time`     DATETIME    NOT NULL,
  `end_time`       DATETIME    NOT NULL,
  `status`         VARCHAR(30) NOT NULL,
  `driver_id`      INT(11)     NOT NULL,
  `spot_id`        INT(11)     NOT NULL,
  PRIMARY KEY (`reservation_id`),
  KEY `driver_id` (`driver_id`),
  KEY `spot_id`   (`spot_id`),
  CONSTRAINT `reservation_ibfk_1`
    FOREIGN KEY (`driver_id`) REFERENCES `DRIVER` (`driver_id`),
  CONSTRAINT `reservation_ibfk_2`
    FOREIGN KEY (`spot_id`) REFERENCES `PARKING_SPOT` (`spot_id`),
  CONSTRAINT `chk_time_order` CHECK (`end_time` > `start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================================
-- MOCK DATA
-- =====================================================================

INSERT INTO `PARKING_LOT` (`lot_id`, `lot_name`, `capacity`) VALUES
(1, 'Central Plaza Parking', 100),
(2, 'North Campus Lot',       80),
(3, 'South Mall Parking',    120),
(4, 'Airport Terminal Lot',  200),
(5, 'Downtown Garage',        60);

INSERT INTO `DRIVER` (`driver_id`, `name`, `phone`, `email`) VALUES
(1, 'Alice Johnson',  '555-1001', 'alice@example.com'),
(2, 'Bob Smith',      '555-1002', 'bob@example.com'),
(3, 'Carol White',    '555-1003', 'carol@example.com'),
(4, 'David Brown',    '555-1004', 'david@example.com'),
(5, 'Eva Martinez',   '555-1005', 'eva@example.com'),
(6, 'Frank Lee',      '555-1006', 'frank@example.com');

INSERT INTO `PARKING_SPOT` (`spot_id`, `spot_type`, `status`, `lot_id`) VALUES
(1,  'standard', 'available', 1),
(2,  'standard', 'occupied',  1),
(3,  'electric', 'available', 1),
(4,  'disabled', 'available', 2),
(5,  'standard', 'occupied',  2),
(6,  'electric', 'available', 2),
(7,  'standard', 'available', 3),
(8,  'disabled', 'occupied',  3),
(9,  'standard', 'available', 4),
(10, 'electric', 'available', 4),
(11, 'standard', 'occupied',  5),
(12, 'disabled', 'available', 5);

INSERT INTO `RESERVATION`
  (`reservation_id`, `start_time`, `end_time`, `status`, `driver_id`, `spot_id`) VALUES
(1,  '2026-03-01 08:00:00', '2026-03-01 10:00:00', 'completed', 1, 1),
(2,  '2026-03-01 09:00:00', '2026-03-01 11:00:00', 'completed', 2, 5),
(3,  '2026-03-02 14:00:00', '2026-03-02 16:00:00', 'completed', 3, 8),
(4,  '2026-03-03 07:00:00', '2026-03-03 09:00:00', 'cancelled', 4, 2),
(5,  '2026-03-04 10:00:00', '2026-03-04 12:00:00', 'active',    5, 3),
(6,  '2026-03-04 13:00:00', '2026-03-04 15:00:00', 'active',    1, 6),
(7,  '2026-03-05 08:00:00', '2026-03-05 10:00:00', 'completed', 2, 9),
(8,  '2026-03-05 11:00:00', '2026-03-05 13:00:00', 'active',    6, 10),
(9,  '2026-03-06 09:00:00', '2026-03-06 11:00:00', 'cancelled', 3, 7),
(10, '2026-03-06 15:00:00', '2026-03-06 17:00:00', 'completed', 4, 11),
(11, '2026-03-07 08:00:00', '2026-03-07 10:00:00', 'active',    5, 12),
(12, '2026-03-07 12:00:00', '2026-03-07 14:00:00', 'completed', 1, 4);

-- =====================================================================
-- TRIGGER: prevent overlapping reservations on the same spot
-- ---------------------------------------------------------------------
-- When a new RESERVATION is being inserted, this trigger fires BEFORE
-- INSERT and checks whether the requested time range overlaps with any
-- existing 'active' or 'completed' reservation for the SAME spot.
--
--   - If an overlap exists -> SIGNAL raises a SQLSTATE '45000' error,
--     blocking the INSERT.
--   - If the new reservation is 'active' AND no overlap exists, the
--     trigger also marks the corresponding PARKING_SPOT as 'occupied'.
--
-- This enforces a real-world business rule that cannot be expressed
-- with simple foreign keys.
-- =====================================================================

DELIMITER $$

CREATE TRIGGER trg_prevent_overlap_reservation
BEFORE INSERT ON `RESERVATION`
FOR EACH ROW
BEGIN
    DECLARE overlap_count INT DEFAULT 0;

    -- Only enforce conflict check when the new reservation is not cancelled
    IF NEW.status <> 'cancelled' THEN

        SELECT COUNT(*) INTO overlap_count
        FROM `RESERVATION` R
        WHERE R.spot_id = NEW.spot_id
          AND R.status IN ('active', 'completed')
          AND NEW.start_time < R.end_time
          AND NEW.end_time   > R.start_time;

        IF overlap_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reservation conflict: this spot is already booked for the requested time range.';
        END IF;

        -- If active reservation passes the check, mark the spot as occupied
        IF NEW.status = 'active' THEN
            UPDATE `PARKING_SPOT`
            SET status = 'occupied'
            WHERE spot_id = NEW.spot_id;
        END IF;

    END IF;
END$$

DELIMITER ;

-- =====================================================================
-- STORED PROCEDURE: sp_GetDriverReservationStats(p_driver_id)
-- ---------------------------------------------------------------------
-- Returns a single-row summary for a given driver:
--   - driver_id
--   - driver_name
--   - total_reservations
--   - active_count
--   - completed_count
--   - cancelled_count
--   - most_used_lot      (the parking lot with the most reservations
--                         by this driver; NULL if no reservation)
--
-- If the driver does not exist, an error is raised.
-- =====================================================================

DELIMITER $$

CREATE PROCEDURE sp_GetDriverReservationStats(IN p_driver_id INT)
BEGIN
    DECLARE v_exists INT DEFAULT 0;

    SELECT COUNT(*) INTO v_exists
    FROM `DRIVER`
    WHERE driver_id = p_driver_id;

    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Driver not found with the given driver_id.';
    END IF;

    SELECT
        D.driver_id,
        D.name AS driver_name,
        COUNT(R.reservation_id) AS total_reservations,
        SUM(CASE WHEN R.status = 'active'    THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN R.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
        SUM(CASE WHEN R.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
        (
            SELECT PL.lot_name
            FROM   `RESERVATION` R2
            JOIN   `PARKING_SPOT` PS ON R2.spot_id = PS.spot_id
            JOIN   `PARKING_LOT`  PL ON PS.lot_id  = PL.lot_id
            WHERE  R2.driver_id = D.driver_id
            GROUP BY PL.lot_id, PL.lot_name
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ) AS most_used_lot
    FROM `DRIVER` D
    LEFT JOIN `RESERVATION` R ON D.driver_id = R.driver_id
    WHERE D.driver_id = p_driver_id
    GROUP BY D.driver_id, D.name;
END$$

DELIMITER ;

-- =====================================================================
-- End of dump
-- =====================================================================
