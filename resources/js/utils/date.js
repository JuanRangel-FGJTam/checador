const formatDate = (date_string) => {
  const myDate = new Date(date_string)
  myDate.setMinutes( myDate.getMinutes() + myDate.getTimezoneOffset() )
  const options = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  }

  return myDate.toLocaleDateString(
    'es-MX', 
    options
  )
}

const formatDatetime = (date) => {
	var d = new Date(date);
	var options = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric', 
    hour:'2-digit', 
    minute:'2-digit' 
  }

	return d.toLocaleDateString(
    'es-MX', 
    options 
  ).toUpperCase();
}

export { 
  formatDate, 
  formatDatetime
}
