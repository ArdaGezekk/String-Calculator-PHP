Copy the Assignment-7 Extending Railways Folder to the directory where your Local Server is installed.

WINDOWS USAGE
- For Appserv
Coppy The Folder to ---> \AppServ\www
then Enter your web browser's address bar of ; Type "http://localhost/Assigment-7_Extending_railways" and go to the address index.php will open automatically.

- For Xampp
Coppy The Folder to ---> xampp\htdocs\
then Enter your web browser's address bar of  http://localhost/Assigment-7_Extending_railways index.php will open automatically.


LINUX USAGE
For LAMP Server
 /var/www/html/  Coppy to  The Folder then your Web browser's address bar of http://localhost/Assigment-7_Extending_railways 

For XAMPP Server
  /opt/lamp/htdocs/   Coppy to  The Folder then your Web browser's address bar of http://localhost/Assigment-7_Extending_railways 


List of symbols
Operators:
+
-
*
/
^

Functions:
sin
cos
abs
pow
Constants
e
pi

1.	Program asksline-by-line ask input from the user, evaluate and print on the screen
a.	read a full line, which potentially includes spaces 
b.	Ask on blank line do not add any additional output
c.	Simply printout the result,  double values and print at least 4 digits after . and properly rounded 
d.	Allow at least 20 operators, 80 characters of input, and 10 function calls. 
2.	Lexer to allow multi character numbers and spaces inside your program.
3.	Allow parsing double values.
4.	Allow unary operator: -
5.	Support variables:
a.	at least allow variables with 8 character names
b.	Using a hashmap PHP, objects
c.	All variables aredouble
d.	Defined  pi (3.141593) and e (2.718282), 
6.	Allow function calls. 
a.	a function call by testing two consecutive lexemes, if first one is an identifier and second is a left parenthesis, then it is a function call. 
b.	Following functions are required: sin, cos, abs pow
c.	Trigonometric functions use degrees
d.	drop pow
e.	To parse multiple parameters, use comma as an operator that has the lowest precedence
f.	Executed the function on right parenthesis 
A full example:
	1+2
3
	2+8*2
318
	2*(3+2)
310
	2+sin(30)
32.5
	x=30
330
	59.2*cos(x*2)
329.6
	4*pow(2,3)
332
	pow(4^3, sin(cos(60)*40*3/2)) / abs(3-5)
34
	e^pi
323.1407
	3*-2
-6

