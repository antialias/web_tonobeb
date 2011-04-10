/**
 * Object PHP_Serializer
 * 	JavaScript to PHP serialize / unserialize class.
 * This class is designed to convert php variables to javascript
 * and javascript variables to php with a php serialize unserialize
 * compatible way.
 *
 * PARSABLE PHP TO JAVASCRIPT VARIABLES:
 * 	[ PHP TYPE ]			[ JAVASCRIPT TYPE ]
 * 	array				Array
 * 	class				Object (*)
 * 	string				String
 * 	boolean				Boolean
 * 	undefined or null		null
 * 	integer / double 		Number
 *
 * PARSABLE JAVASCRIPT TO PHP VARIABLES:
 *	[ JAVASCRIPT TYPE ]		[ PHP TYPE ]
 *	Array				array
 *	Object				class (*)
 *	String				string
 *	Boolean				boolean
 *	null				null
 *	Number				int or double
 *	Date				class
 *	Error				class
 *	Function			anything (*)
 *	__class				anything (*)
 *
 * (*) NOTE:
 * Any PHP serialized class requires the native PHP class to be used, then it's not a
 * PHP => JavaScript converter, it's just a usefull serilizer class for each
 * compatible JS and PHP variable types.
 * However is possible to change public parameters.
 * Lambda, Resources or other dedicated PHP variables are not usefull for JavaScript.
 * (i.e.
 * 	$v = create_function('', 'return 1;'); serialize($v);
 *	$conn = mydb_connect(); serialize($conn);
 * )
 * There are same restrictions for javascript functions too then these will not be sent
 * (but will be filtered / ignored automatically).
 * _____________________________________________
 *
 * EXAMPLE:
 *	var php = new PHP_Serializer();
 *	alert(php.unserialize(php.serialize(somevar)));
 *	// should alert the original value of somevar
 * ---------------------------------------------
 * @author              Andrea Giammarchi
 * @site		www.devpro.it
 * @date                2005/11/26
 * @lastmod             2005/12/15 12:50 [faster unserialize for IE browser]
 * @credits		Special thanks to Fabio Sutto for some ideas and some debug
 *			Special thanks to kentaromiura for a faster loop idea while unserialize
 * @version             1.6, tested on FireFox 1.0.7, FireFox 1.5, IE 6 SP2 and Opera 8
 */
function PHP_Serializer() {
	this.__cut = (String(Object).indexOf('(')!=16)?9:10;
}
function PHP_Serializer__String(__s) {
	return ('s:'+__s.length+':"'+__s+'";');
}
function PHP_UnSerializer__String() {
	this.__c += 2;
	var sls = this.__s.substr(this.__c,(this.__s.indexOf(':',this.__c)-this.__c));
	var sli = parseInt(sls);
	sls = this.__c + sls.length + 2;
	this.__c = sls + sli + 2;
	return this.__s.substr(sls,sli);
}
function PHP_Serializer__Boolean(__s) {
	return ('b:'+(__s==false?'0':'1')+';');
}
function PHP_UnSerializer__Boolean() {
	var tmp = (this.__s.substr((this.__c+2),1)=='1'?true:false);
	this.__c += 4;
	return tmp;
}
function PHP_Serializer__Number(__s) {
	__s = String(__s);
	return ((__s.indexOf('.')==-1)?'i:'+__s+';':'d:'+__s+';');
}
function PHP_UnSerializer__Number() {
	var sli = this.__s.indexOf(';',(this.__c+1))-2;
	var tmp = Number(this.__s.substr((this.__c+2),(sli-this.__c)));
	this.__c = sli + 3;
	return tmp;
}
function PHP_Serializer__Function() {
	return '';
}
function PHP_Serializer__Undefined() {
	return 'N;';
}
function PHP_UnSerializer__Undefined() {
	this.__c += 2;
	return null;
}
function PHP_Serializer__Common_ArrayObject(__s) {
	var n;
	var a = 0;
	var ser = new Array();
	for(var b in __s) {
		n = (__s[b] == null);
		if(n || (__s[b].constructor != Function && b != '__class')) {
			ser[a++]=((!isNaN(b))?this.__number(b):this.__string(b))+
			(n?this.__undefined():this[__s[b].constructor](__s[b]));
		}
	}
	return [a,ser.join('')];
}
function PHP_UnSerializer__Common_ArrayObject(tmp) {
	this.__c += 2;
	var a = this.__s.indexOf(':',this.__c);
	var k = parseInt(this.__s.substr(this.__c,(a-this.__c))) + 1;
	this.__c = a + 2;
	while(--k)
		tmp[this[this.__s.substr(this.__c,1)]()] = this[this.__s.substr(this.__c,1)]();
	return tmp;
}
function PHP_Serializer__Object(__s) {
	var o = String(__s.constructor);
	var oname = o.substr(this.__cut,o.indexOf('(')-this.__cut);
	var ser = this.__common_array_object(__s);
	return ('O:'+oname.length+':"'+oname+'":'+ser[0]+':{'+ser[1]+'}');
}
function PHP_UnSerializer__Object() {
	var tmp = 's'+this.__s.substr(++this.__c,(this.__s.indexOf(':',(this.__c+3))-this.__c))+';';
	var a = tmp.substr(2,(tmp.indexOf(':',2)-2));
	var o = tmp.substr((a.length+4),parseInt(a));
	if(eval("typeof("+o+") == 'undefined'"))
		eval('function '+o+'(){}');
	this.__c += (tmp.length-3);
	eval('tmp = this.__common(new '+o+'());');
	++this.__c;
	return tmp;
}
function PHP_Serializer__Array(__s) {
	var ser = this.__common_array_object(__s);
	return ('a:'+ser[0]+':{'+ser[1]+'}');
}
function PHP_UnSerializer__Array() {
	var tmp = this.__common(new Array());
	++this.__c;
	return tmp;
}
function PHP_Serializer__serialize(what) {
	if(what==null)
		var ser = this.__undefined();
	else if(!this[what.constructor])
		var ser = this[Object](what);
	else
		var ser = this[what.constructor](what);
	return ser;
}
function PHP_UnSerializer__unserialize(what) {
	this.__c = 0;
	this.__s = what;
	delete what;
	return this[this.__s.substr(this.__c,1)]();
}
PHP_Serializer.prototype[Boolean] = PHP_Serializer__Boolean;
PHP_Serializer.prototype[Number] =
PHP_Serializer.prototype.__number = PHP_Serializer__Number;
PHP_Serializer.prototype[String] =
PHP_Serializer.prototype.__string = PHP_Serializer__String;
PHP_Serializer.prototype[Function] = PHP_Serializer__Function;
PHP_Serializer.prototype[Date] = 
PHP_Serializer.prototype[Error] = 
PHP_Serializer.prototype[Object] = PHP_Serializer__Object;
PHP_Serializer.prototype[Array] = PHP_Serializer__Array;
PHP_Serializer.prototype.__common_array_object = PHP_Serializer__Common_ArrayObject;
PHP_Serializer.prototype.__undefined = PHP_Serializer__Undefined;
PHP_Serializer.prototype.serialize = PHP_Serializer__serialize;
PHP_Serializer.prototype.s = PHP_UnSerializer__String;
PHP_Serializer.prototype.b = PHP_UnSerializer__Boolean;
PHP_Serializer.prototype.i =
PHP_Serializer.prototype.d = PHP_UnSerializer__Number;
PHP_Serializer.prototype.N = PHP_UnSerializer__Undefined;
PHP_Serializer.prototype.__common = PHP_UnSerializer__Common_ArrayObject;
PHP_Serializer.prototype.O = PHP_UnSerializer__Object;
PHP_Serializer.prototype.a = PHP_UnSerializer__Array;
PHP_Serializer.prototype.unserialize = PHP_UnSerializer__unserialize;