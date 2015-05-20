SELECT PrefixID, AVG(Score) as avgScore
FROM Scores
GROUP BY PrefixID
ORDER BY avgScore Desc;