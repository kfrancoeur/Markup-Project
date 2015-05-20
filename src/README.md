This is the primary source for the project; it's a php class built as defined in the parent folder's readme.
The public function calls are as follows:

#__construct($filename)
The constructor; the passed variable is either the filename to be parsed
or an array of file names.  It can be null if at instantiation the user
doesn't wish to go through a full parse yet (such as to simply pull scores
from DB).

#set_file($filename)
Used to set a file if the user wishes to do so after instantiation.
Accepts a file name or an array of file names.

#save_data()
Saves currently parsed data to the database.

#pid_score($PrefixID)
Pulls all scores based on a specific "filename prefix" as defined in the parent readme.

#date_score($StartDate, $EndDate)
Pulls all scores within a specific date range; inclusive to dates passed.

#retrieve_high()
Pulls the highest score and their prefixID, returning as a hybrid array;
Value associated with key "Score" is the highest score, and indexed rows are prefixes
that have that value.

#retrieve_low()
Pulls the lowest score; everything else is the same as retrieve_high().

#get_scores()
Returns scores for current parsed files.

#get_file_name()
Returns the name of the current files.
