<?php
	$statement = "";
	
	// Add itemType to Payments
	// Add SodaBalance and SnackBalance to User
	// Create REQUESTS table
	
	/*
	 	$statement = "UPDATE Item SET TotalExpenses = TotalExpenses - 3.49, BackstockQuantity = BackstockQuantity - 12, TotalCans = TotalCans - 12 where ID = 29";
		
		$statement = "UPDATE ITEM SET Price = 0.40 WHERE name = 'Swiss Miss Dark Chocolate';";
		$statement = "ALTER TABLE ITEM ADD COLUMN ImageURL TEXT;"
		$statement = "ALTER TABLE ITEM ADD COLUMN ThumbURL TEXT;"
		$statement = "ALTER TABLE ITEM ADD COLUMN UnitName TEXT;"
		$statement = "UPDATE ITEM SET Retired = 1 WHERE name = 'Cherry Zero';";
		
		$statement = "CREATE TABLE Item(id integer PRIMARY KEY AUTOINCREMENT, name text, date text, chartcolor text, totalcans integer, backstockquantity integer, shelfquantity integer, price real,  totalincome real, totalexpenses real )";
		$statement = "CREATE TABLE Restock(itemid integer, date text, numberofcans integer, cost real )";
		$statement = "CREATE TABLE Daily_Amount(itemid integer, date text, backstockquantitybefore integer, backstockquantity integer, shelfquantitybefore integer, shelfquantity integer, price real, restock integer )";
		$statement = "DROP TABLE Item;";
	*/
	
	if( $statement != "" ) {
		echo "Executing......<br>";
	
		$db->exec($statement);
	
		echo "DONE!<br><br>";
	}
?>