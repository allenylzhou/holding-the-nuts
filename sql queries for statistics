----GET TOTAL PROFIT
select SUM(AMOUNT_OUT - AMOUNT_IN)
from Game
where USER_ID = 0

--- GET PROFIT BY MONTH FOR A USER_ID
select extract(month from START_DATE) as mon, extract(year from START_DATE) as year, SUM(AMOUNT_OUT - AMOUNT_IN)
from Game
where USER_ID = 0
group by extract(year from START_DATE), extract(month from START_DATE)
order by 1, 2;


---- GET TOTAL HOURS FOR MONTH

select extract(month from START_DATE) as mon, SUM(extract(hour from cast(END_DATE as timestamp)) - extract(hour from cast(START_DATE as timestamp))) as numhours
from Game
where USER_ID = 0
group by extract(year from START_DATE), extract(month from START_DATE)
order by 1, 2;


---- GET TOTAL MINUTES FOR MONTH
select extract(month from START_DATE) as mon, SUM(extract(minute from cast(END_DATE as timestamp)) - extract(minute from cast(START_DATE as timestamp))) as numminutes
from Game
where USER_ID = 0
group by extract(year from START_DATE), extract(month from START_DATE)
order by 1, 2;


---- GET TOTAL PROFIT BY DAY OF WEEK
select to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY'), SUM(AMOUNT_OUT - AMOUNT_IN)
from Game
where USER_ID = 0
group by to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY');


---- NESTED AGGREGATE
---- GET BEST AVERAGE DAY OF THE WEEK FOR BETTING
WITH USER_WINNING_ON_DAY AS (
	SELECT to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY') AS DAY_OF_WEEK,
        AVG(AMOUNT_OUT-AMOUNT_IN) as WINNINGS
	FROM   GAME
	WHERE  USER_ID = :userId
	GROUP BY to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY'))
SELECT DAY_OF_WEEK, WINNINGS
FROM   USER_WINNING_ON_DAY
WHERE  WINNINGS >= ALL (SELECT MAX(A.WINNINGS)
						FROM USER_WINNING_ON_DAY A)

		
---- NESTED AGGREGATE				
---- GET BEST DAY SO FAR
WITH USER_WINNING_ON_DAY AS (
  SELECT TRUNC(START_DATE) AS GAME_DAY,
		 AVG(AMOUNT_OUT-AMOUNT_IN) as WINNINGS
  FROM   GAME
  WHERE  USER_ID = :userId
  GROUP BY TRUNC(START_DATE))
SELECT GAME_DAY, WINNINGS
FROM   USER_WINNING_ON_DAY
WHERE  WINNINGS >= ALL (SELECT MAX(A.WINNINGS)
						FROM USER_WINNING_ON_DAY A)
						
						
---- DIVSION		
---- GET PEOPLE WHO HAVE THE SAME BACKER
select distinct(hb.horse) as otherUser
from horse_backers hb
where not exists (select distinct(hb1.backer)
				from horse_backers hb1
				where hb1.horse = :userId
				minus
				select distinct(hb2.backer) 
				from horse_backers hb2
				where hb2.horse = hb.horse)


