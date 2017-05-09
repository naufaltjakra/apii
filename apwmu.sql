-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 02 Mei 2017 pada 10.46
-- Versi Server: 10.1.16-MariaDB
-- PHP Version: 5.5.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apwmu`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `username` varchar(30) NOT NULL,
  `nim` varchar(14) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `api_key` varchar(255) NOT NULL,
  `judul_ta` varchar(60) NOT NULL,
  `ipk` float NOT NULL,
  `pembimbing_ta` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`username`, `nim`, `password_hash`, `created_at`, `api_key`, `judul_ta`, `ipk`, `pembimbing_ta`) VALUES
('qwerty', '12345678901234', '$2a$10$7079b1ebc1355f15cd20duTrat3uf0qkB0EpF54.LSyswXpEjBgZ6', '2017-04-30 04:55:08', '1bcf7478d80f74a754c365e581196090', '', 0, ''),
('ffg', '2222', '$2a$10$b33ce8543f1c472250ac8ue7m15r0pU.O6qgB2DTb9vMtl90t3EAm', '2017-05-01 12:48:14', '54bca316895bd7f60d827e6d1694d984', '', 0, ''),
('coba', '24010314120001', '$2a$10$8ba1e6464e900b4bfa9bfO/JVckdswdpiuyWVA.UmPAoUPPdaCZ8.', '2017-04-30 04:23:51', '873d214edb7869580995de8f1ccd9413', '', 0, ''),
('Garfianto Dwi', '24010314120002', '$2a$10$a4d01402296746aff65afeB4IvUo4tD5B/aKOUTdiD80AkxQkfIyG', '2017-05-02 07:01:15', '349eaf70e6a56c8b32e9cfdb95d37e9c', 'Aplikasi x berbasis android', 3.5, 'Harum Mualimus'),
('sherian', '24010314120005', '$2a$10$9ede734d8f173de4d8a0auFCXQuVKzcle6jsjSd74knqeF0yqhKFe', '2017-05-02 07:01:08', 'a00895f92c8ae1c63542955ae0174b3b', 'Machine learning for Cybersecurity', 4, 'Pak Eko'),
('fian1', '24010314120043', '$2a$10$064d12bdec235d29a7b88eMLbuhJGcRpwraAi/l5sl/ySdGhq7FaO', '2017-04-30 03:09:59', '4d4cb88237ffaccf61f1e5017ad67b5a', '', 0, ''),
('fian', '24010314120044', '$2a$10$75fe765f9aec3a7ff84bfuSvoOOdSAAOC1yLqQaxl5KRA.fXY3hxe', '2017-04-30 03:08:41', 'b811a4829de4309b80f6d3598f1e863e', '', 0, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`nim`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
