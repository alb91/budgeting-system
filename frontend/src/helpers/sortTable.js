export const sortTable = (expenses, column, direction) => { 
  switch (true) {
    case direction === 'asc' && column === 'name': 
    expenses.sort((a, b) => {
      return a.name.localeCompare(b.name);
    });
    break; 
    case direction === 'desc' && column === 'name': 
    expenses.sort((a, b) => {
      return b.name.localeCompare(a.name);
    });
    break; 
    case direction === 'asc' && column === 'date': 
      expenses.sort((a, b) => {
      return new Date(a.date) - new Date(b.date) 
    });
    break; 
    case direction === 'desc' && column === 'date':
      expenses.sort((a, b) => {
      return new Date(b.date) - new Date(a.date) 
    });
    break; 
    case direction === 'asc' && column === 'amount':
      expenses.sort((a, b) => {
      return parseFloat(a.amount) - parseFloat(b.amount) 
    }); 
    break; 
    case direction === 'desc' && column === 'amount':
        expenses.sort((a, b) => {
      return parseFloat(b.amount) - parseFloat(a.amount) 
    }); 
    break;
    case direction === 'asc' && column === 'start': 
      expenses.sort((a, b) => {
      return new Date(a.start_date) - new Date(b.start_date) 
    });
    break; 
    case direction === 'desc' && column === 'start':
      expenses.sort((a, b) => {
      return new Date(b.start_date) - new Date(a.start_date) 
    });
    break;
    case direction === 'asc' && column === 'end': 
      expenses.sort((a, b) => {
      return new Date(a.end_date) - new Date(b.end_date) 
    });
    break; 
    case direction === 'desc' && column === 'end':
      expenses.sort((a, b) => {
      return new Date(b.end_date) - new Date(a.end_date) 
    });
    break;
    case direction === 'asc' && column === 'budget':
      expenses.sort((a, b) => {
      return parseFloat(a.budget) - parseFloat(b.budget) 
    }); 
    break; 
    case direction === 'desc' && column === 'budget':
        expenses.sort((a, b) => {
      return parseFloat(b.budget) - parseFloat(a.budget) 
    }); 
    break;
    case direction === 'asc' && column === 'spent':
      expenses.sort((a, b) => {
      return parseFloat(a.spent) - parseFloat(b.spent) 
    }); 
    break; 
    case direction === 'desc' && column === 'spent':
        expenses.sort((a, b) => {
      return parseFloat(b.spent) - parseFloat(a.spent) 
    }); 
    break;
     case direction === 'asc' && column === 'remaining':
      expenses.sort((a, b) => {
      return parseFloat(a.remaining) - parseFloat(b.remaining) 
    }); 
    break; 
    case direction === 'desc' && column === 'remaining':
        expenses.sort((a, b) => {
      return parseFloat(b.remaining) - parseFloat(a.remaining) 
    }); 
    break;
  }
  return expenses; 
}