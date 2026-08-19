export const sortTable = (items, columns, columnName, direction) => { 
  const column = columns[columnName]

  return [...items].sort((a, b) => {
    const first = a[column.field]
    const second = b[column.field]

    let result; 

    if(column.type === 'text'){
      result = first.localeCompare(second);
    } else if (column.type === 'number'){
      result = Number(first) - Number(second);
    } else if (column.type === 'date') {
      result = new Date(first) - new Date(second);
    }
    
    return direction === 'asc' ? result : -result; 
  });
}