<h1>单进程转多进程</h1>


<p>
    适用于批量处理数据的时候，多个进程同时处理数据会提高效率
</p>
<p>
    原理：将需要分割的参数分割后存入临时文件，临时起个进程去处理分割后的参数
</p>
<p>
    此版本基于CI框架开发，但是跟框架耦合度很低，如果想放到别的框架需做如下修改：
    
    <ul>
        <li>
            修改OneToMoreProc类中与APPPATH有关的相关参数
        </li>
        <li>
            修改OneToMoreProc这个类所继承的父类名称
        </li>
        <li>
            修改OneToMoreProc类中openChildProcessById这个方法里面的$phpPath、$indexPath两个参数的值的获取方式；以及$cmd的格式
        </li>
    </ul>
</p>
