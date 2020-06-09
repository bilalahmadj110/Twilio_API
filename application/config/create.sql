
CREATE TABLE `request` (
  `id` int(11) NOT NULL,
  `from_ph_num` varchar(15) NOT NULL,
  `to_ph_num` varchar(15) NOT NULL,
  `speech_text` varchar(1000) NOT NULL,
  `status` varchar(100) DEFAULT NULL,
  `response` varchar(50) DEFAULT NULL,
  `sid` varchar(100) DEFAULT NULL
);