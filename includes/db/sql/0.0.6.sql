
-- DataFormFields
CREATE TABLE {pref}inf_DataFormField (
  Id int(20) DEFAULT NULL,
  FormId int(20) DEFAULT NULL,
  GroupId int(20) DEFAULT NULL,
  DataType int(20) DEFAULT NULL,
  Label varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  Name varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci