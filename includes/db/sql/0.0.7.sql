CREATE TABLE {pref}infm_api_log (
  id int(20) NOT NULL AUTO_INCREMENT,
  created_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  user varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  service varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  caller longtext COLLATE utf8_unicode_ci,
  result longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci


CREATE TABLE {pref}infm_contact_group (
  Id int(20) NOT NULL,
  GroupName varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  GroupCategoryId int(20) DEFAULT NULL,
  created_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

CREATE TABLE {pref}infm_tag_cat (
  Id int(20) NOT NULL,
  CategoryName varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  created_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
CREATE TABLE {pref}inf_DataFormField (
  Id int(20) DEFAULT NULL,
  FormId int(20) DEFAULT NULL,
  GroupId int(20) DEFAULT NULL,
  DataType int(20) DEFAULT NULL,
  Label varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  Name varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci