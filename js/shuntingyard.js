function shuntingYard(formula) {
    var priority = {
        '&&': 2,
        '||': 1
    };
    var output = [];
    var opstack = []; // operator stack
    for (var i = 0, _i = formula.length; i < _i; i++) {
        if (formula[i].type === 'primary') {
            output.push(formula[i]);
        } else if (formula[i].type === 'operator') {
            var o1 = formula[i].value;
            if (opstack.length > 0) {
                var o2 = opstack[opstack.length - 1].value;
                while (opstack.length > 0 && isLeftAssociative(o1) && priority[o1] <= priority[o2] || priority[o1] < priority[o2]) {
                    output.push(opstack.pop());
                    if (opstack.length > 0) o2 = opstack[opstack.length - 1].value;
                }
            }
            opstack.push(formula[i]);
        } else if (formula[i].type === 'left-parenthesis') {
            opstack.push(formula[i]);
        } else if (formula[i].type === 'right-parenthesis') {
            while(opstack.length > 0 && opstack[opstack.length - 1].type !== 'left-parenthesis') {
                output.push(opstack.pop());
            }
            if (opstack.length === 0) {
                throw new Error('mismatched parentheses');
            }
            opstack.pop(); // remove left parenthesis
        }
    }
    var i = opstack.length;
    while (i > 0) {
        var o = opstack.pop();
        if (o.type === 'left-parenthesis' || o.type === 'right-parenthesis') {
            throw new Error('mismatched parentheses');
        }
        output.push(o);
        i--;
    }
    return output;
}
function isLeftAssociative(op) {
    return {
        '&&': true,
        '||': true
    }[op];
}
//module.exports = shuntingYard;