# --- Todo: Link: https://stackoverflow.com/a/30494102
# |==============================================================|
# --- Todo: Post multiple dynamic relation-table  						---|
# --- Todo: Store "post.name" = "gr" ( gr: general_relation ) --|
# --- Todo: Store "post.name_id" = "gr.id"										---|
# |==============================================================|

SELECT *
FROM persons

	LEFT JOIN person_phone
		ON person_phone.person_id = person.person_id

	LEFT JOIN mobile_phone
		ON mobile_phone.mobile_phone_id = person_phone.related_id
		AND person_phone.related_table = 'mobile_phone'

	LEFT JOIN home_phone
		ON home_phone.home_phone_id = person_phone.related_id
		AND person_phone.related_table = 'home_phone'