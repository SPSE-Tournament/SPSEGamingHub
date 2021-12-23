-- phpMyAdmin SQL Dump
-- version 5.0.4deb2
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost:3306
-- Vytvořeno: Čtv 23. pro 2021, 11:28
-- Verze serveru: 10.5.12-MariaDB-0+deb11u1
-- Verze PHP: 7.2.34-28+0~20211119.67+debian11~1.gbpf24e81

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `tournament`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `eventparticipation`
--

CREATE TABLE `eventparticipation` (
  `eventp_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `events`
--

CREATE TABLE `events` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `event_name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `game_id` int(11) NOT NULL,
  `event_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `event_playerlimit` int(11) NOT NULL,
  `game_playerlimitperteam` int(11) NOT NULL,
  `event_url` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `bracket_status` set('live','dead') COLLATE utf8_czech_ci NOT NULL DEFAULT 'dead',
  `bracket_format` enum('single','double','','') COLLATE utf8_czech_ci NOT NULL DEFAULT 'single',
  `event_status` set('scheduled','live','finished','reglocked') COLLATE utf8_czech_ci NOT NULL DEFAULT 'scheduled',
  `event_winner` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `games`
--

CREATE TABLE `games` (
  `game_id` int(10) UNSIGNED NOT NULL,
  `game_name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `game_short_name` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `game_rules` mediumtext COLLATE utf8_czech_ci DEFAULT NULL,
  `game_playerlimitperteam` int(11) NOT NULL,
  `game_background` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `game_icon` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `logs`
--

CREATE TABLE `logs` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `log_message` longtext COLLATE utf8_czech_ci NOT NULL,
  `log_type` set('login','register','event_register','event_edit','event_join','event_drop','user_report','user_verify','team_creation','team_edit','team_join','team_verify','team_removal','team_leave','game_add','game_edit','game_drop','bracket_creation','bracket_drop','message_sent','user_admin') COLLATE utf8_czech_ci NOT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `log_userip` varchar(45) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `matches`
--

CREATE TABLE `matches` (
  `match_id` int(10) UNSIGNED NOT NULL,
  `match_first_team` varchar(25) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
  `match_second_team` varchar(25) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
  `match_round` varchar(10) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `match_first_team_score` int(11) NOT NULL DEFAULT 0,
  `match_second_team_score` int(11) NOT NULL DEFAULT 0,
  `match_first_team_seed` int(11) DEFAULT NULL,
  `match_second_team_seed` int(11) DEFAULT NULL,
  `match_bracket_seed` int(11) NOT NULL,
  `match_status` set('live','scheduled','finished') CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL DEFAULT 'scheduled',
  `match_description` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabulky `messages`
--

CREATE TABLE `messages` (
  `message_id` int(10) UNSIGNED NOT NULL,
  `message` varchar(300) COLLATE utf8_czech_ci NOT NULL,
  `message_perex` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `message_type` set('message','invite','trash') COLLATE utf8_czech_ci NOT NULL,
  `message_status` set('read','unread','trash','') COLLATE utf8_czech_ci NOT NULL DEFAULT 'unread',
  `message_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_senderid` int(10) UNSIGNED NOT NULL,
  `user_receiverid` int(10) UNSIGNED NOT NULL,
  `user_sendername` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `user_receivername` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `invite_team_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `registrations`
--

CREATE TABLE `registrations` (
  `registrations_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(45) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `user_email` varchar(150) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `user_password` mediumtext CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `user_hash` varchar(128) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabulky `teamparticipation`
--

CREATE TABLE `teamparticipation` (
  `teamp_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `teams`
--

CREATE TABLE `teams` (
  `team_id` int(10) UNSIGNED NOT NULL,
  `team_name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `team_captain_id` int(10) UNSIGNED NOT NULL,
  `game_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `name_r` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `surname` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `password` longtext COLLATE utf8_czech_ci NOT NULL,
  `admin` int(1) NOT NULL,
  `watchman` int(1) NOT NULL DEFAULT 0,
  `rootmaster` int(1) NOT NULL DEFAULT 0,
  `user_hexid` varchar(4) COLLATE utf8_czech_ci NOT NULL,
  `user_verified` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `eventparticipation`
--
ALTER TABLE `eventparticipation`
  ADD PRIMARY KEY (`eventp_id`),
  ADD KEY `user_id` (`user_id`,`event_id`,`team_id`);

--
-- Klíče pro tabulku `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Klíče pro tabulku `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`);

--
-- Klíče pro tabulku `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Klíče pro tabulku `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `match_first_team` (`match_first_team`,`match_second_team`);

--
-- Klíče pro tabulku `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_senderid` (`user_senderid`,`user_receiverid`,`invite_team_id`);

--
-- Klíče pro tabulku `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registrations_id`);

--
-- Klíče pro tabulku `teamparticipation`
--
ALTER TABLE `teamparticipation`
  ADD PRIMARY KEY (`teamp_id`),
  ADD KEY `user_id` (`user_id`,`team_id`);

--
-- Klíče pro tabulku `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `team_captain_id` (`team_captain_id`,`game_id`);

--
-- Klíče pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `eventparticipation`
--
ALTER TABLE `eventparticipation`
  MODIFY `eventp_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registrations_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `teamparticipation`
--
ALTER TABLE `teamparticipation`
  MODIFY `teamp_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
