package ttt;
public class phptest{
   

    public String foo;

   
    public String test(String str) {
        if(str.equals("")) {
            str = "Your string was empty. ";
        }
        return str;
    }

   
    public String whatisfoo() {
        return "foo is " + foo;
    }


   
    public static void main(String args[]) {
        phptest p = new phptest();

        if(args.length == 0) {
            String arg = "";
            System.out.println(p.test(arg));
        }else{
            for (int i = 0; i < args.length; i++) {
                String arg = args[i];
                System.out.println(p.test(arg));
            }
        }
    }
}
