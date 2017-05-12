	.text
.globl main
	.type	main, @function
main:
	pushq	%rbp
	movq	%rsp, %rbp
	nop
	movl	$0, %eax
	leave
	ret
